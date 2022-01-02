<?php

namespace De\Idrinth\Travian;

use PDO;
use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeffCall
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post, $id, $key=''): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $data = [
            'id' => $id,
            'key' => $key,
            'now' => date('Y-m-d H:i:s'),
            'added' => false,
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'session' => $_SESSION,
        ];
        if (!Uuid::isValid($id)) {
            header('Location: /deff-call', true, 307);
            return;
        }
        $stmt = $this->database->prepare("SELECT * FROM deff_calls WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $data['target'] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $data['target']) {
            header('Location: /deff-call', true, 307);
            return;
        }
        if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
            $this->database
                ->prepare("INSERT IGNORE INTO user_deff_call (user, deff_call) VALUES(:user, :deff_call)")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':deff_call' => $data['target']['aid']
                ]);
            if ($data['target']['key'] === $key) {
                $this->database
                    ->prepare("UPDATE user_deff_call SET advanced=1 WHERE user=:user AND deff_call=:deff_call")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':deff_call' => $data['target']['aid']
                    ]);
            }
        }
        if (isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts'] > 0) && isset($post['time']) && isset($post['account']) && time() < strtotime($data['target']['arrival'])) {
            $stmt = $this->database->prepare("INSERT INTO deff_call_supports (creator, scouts, troops, arrival, deff_call, account) VALUES(:creator, :scouts, :troops, :arrival, :deff_call, :account)");
            $stmt->execute([
                ':scouts' => intval($post['scouts'], 10),
                ':troops' => intval($post['troops'], 10),
                ':account' => $post['account'],
                ':creator' => $_SESSION['id'] ?? 0,
                ':arrival' => strtotime($post['time']),
                ':deff_call' => $data['target']['aid'],
            ]);
            $data['added'] = true;
        }
        $stmt = $this->database->prepare("SELECT deff_call_supports.*,users.name,users.discriminator FROM deff_call_supports LEFT JOIN users ON deff_call_supports.creator=users.aid WHERE deff_call=:id");
        $stmt->execute([':id' => $data['target']['aid']]);
        $data['supports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $twig->display('deff-call.twig', $data);
    }
}
