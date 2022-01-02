<?php

namespace De\Idrinth\Travian;

use PDO;
use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Profile
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        if (!isset($_SESSION['id']) || !$_SESSION['id']) {
            header('Location: /login', true, 307);
            return;
        }
        $stmt = $this->database->prepare(
            "SELECT user_deff_call.advanced, deff_calls.arrival, deff_calls.key, deff_calls.id, deff_calls.world, deff_calls.x, deff_calls.y "
            . "FROM user_deff_call "
            . "INNER JOIN deff_calls "
            . "ON deff_calls.aid=user_deff_call.deff_call "
            . "AND user_deff_call.user=:user"
        );
        $stmt->execute([':user' => $_SESSION['id']]);
        $data = [
            'id' => $id,
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'session' => $_SESSION,
            'deff_calls' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
        $twig->display('profile.twig', $data);
    }
}
