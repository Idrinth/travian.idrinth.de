<?php

namespace De\Idrinth\Travian;

use PDO;
use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeffCallCreation
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $data = [
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'session' => $_SESSION,
        ];
        if (isset($post['x']) && isset($post['y']) && isset($post['world']) && isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts'] > 0) && isset($post['time'])) {
            $uuid = Uuid::uuid6();
            $stmt = $this->database->prepare(
                "INSERT INTO deff_calls (id, `key`, scouts, troops, `x`, `y`, world, creator, arrival) "
                . "VALUES (:id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival)"
            );
            if (strpos($post['world'], 'https://') === 0) {
                $post['world'] = substr($post['world'], 8);
            }
            $post['world'] = explode('/', $post['world'])[0];
            $key = Uuid::uuid4();
            $stmt->execute([
                ':id' => $uuid,
                ':key' => $key,
                ':world' => strtolower($post['world']),
                ':x' => intval($post['x'], 10),
                ':y' => intval($post['y'], 10),
                ':scouts' => intval($post['scouts'], 10),
                ':troops' => intval($post['troops'], 10),
                ':arrival' => date('Y-m-d H:i:s', strtotime($post['time'])),
                ':creator' => $_SESSION['id'] ?? 0
            ]);
            header('Location: /deff-call/' . $uuid . '/' . $key, true, 307);
            return;
        }
        $twig->display('create-deff-call.twig', $data);
    }
}
