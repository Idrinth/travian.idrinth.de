<?php

namespace De\Idrinth\Travian;

use PDO;
use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Alliance
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post, $id = '', $key=''): void
    {
        if (($_SESSION['id'] ?? 0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        if ($id && $key) {
            $stmt = $this->database->prepare("SELECT aid FROM alliances WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $alliance = $stmt->fetchColumn();
            if (!$alliance) {
                header('Location: /profile', true, 303);
                return;
            }
            $this->database
                ->prepare("INSERT IGNORE INTO user_alliance (user, alliance) VALUES (:user, :alliance)")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':alliance' => $alliance
                ]);
            header('Location: /alliance/'.$id, true, 303);
            return;
        }
        if ($id) {
            $stmt = $this->database->prepare("SELECT * FROM alliances WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $alliance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (false === $alliance) {
                header('Location: /profile', true, 303);
                return;
            }
            $stmt = $this->database->prepare("SELECT user_alliance.* FROM user_alliance WHERE alliance=:alliance AND user=:user");
            $stmt->execute([':alliance' => $alliance['aid'], ':user' => $_SESSION['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($post['ingame'])) {
                $this->database
                    ->prepare("UPDATE user_alliance SET `name`=:name WHERE alliance=:alliance AND user=:user")
                    ->execute([':alliance' => $alliance['aid'], ':user' => $_SESSION['id'], ':name' => $post['ingame']]);
            } elseif (isset($post['regen-key']) && in_array($user['rank'], ['High Council', 'Creator'], true)) {
                $this->database
                    ->prepare("UPDATE alliances SET `key`=:key WHERE id=:id")
                    ->execute([':id' => $id, ':key' => Uuid::uuid4()]);
            } elseif (isset($post['user']) && isset($post['rank']) && $post['user'] != $_SESSION['id'] && in_array($user['rank'], ['High Council', 'Creator'], true)) {
                if ($post['rank'] === 'Kick') {
                    $this->database
                        ->prepare("DELETE FROM user_alliance WHERE alliance=:alliance AND user=:user")
                        ->execute([':alliance' => $alliance['aid'], ':user' => $post['user']]);
                } elseif ($post['rank'] !== 'Creator') {
                    $this->database
                        ->prepare("UPDATE user_alliance SET `rank`=:rank WHERE alliance=:alliance AND user=:user")
                        ->execute([':alliance' => $alliance['aid'], ':user' => $post['user'], ':rank' => $post['rank']]);
                }
            }
            $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
            $stmt = $this->database->prepare("SELECT user_alliance.*, users.aid, users.name as discord, users.discriminator FROM user_alliance INNER JOIN users ON users.aid=user_alliance.user WHERE alliance=:alliance");
            $stmt->execute([':alliance' => $alliance['aid']]);
            $stmt2 = $this->database->prepare("SELECT deff_calls.id, deff_calls.arrival, deff_calls.world, deff_calls.x, deff_calls.y FROM deff_calls WHERE alliance=:alliance");
            $stmt2->execute([':alliance' => $alliance['aid']]);
            $stmt3 = $this->database->prepare("SELECT * FROM hero WHERE alliance=:alliance");
            $stmt3->execute([':alliance' => $alliance['aid']]);
            $twig->display('alliance.twig', [
                'lang' => $_COOKIE['lang'] ?? 'en',
                'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
                'session' => $_SESSION,
                'alliance' => $alliance,
                'players' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'deff_calls' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
                'heroes' => $stmt3->fetchAll(PDO::FETCH_ASSOC),
            ]);
            return;
        }
        if (isset($post['name']) && isset($post['world'])) {
            if (strpos($post['world'], 'https://') === 0) {
                $post['world'] = substr($post['world'], 8);
            }
            $post['world'] = explode('/', $post['world'])[0];
            $id = Uuid::uuid6();
            $this->database
                ->prepare("INSERT INTO alliances (id, name, world, `key`) VALUES (:id, :name, :world, :key)")
                ->execute([
                    ':id' => $id,
                    ':name' => $post['name'],
                    ':world' => $post['world'],
                    ':key' => Uuid::uuid4(),
                ]);
            $this->database
                ->prepare("INSERT INTO user_alliance (user, alliance, rank) VALUES (:user, :alliance, 'Creator')")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':alliance' => $this->database->lastInsertId()
                ]);
            header('Location: /profile', true, 303);
            return;
        }
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $twig->display('alliance-create.twig', [
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'session' => $_SESSION,
        ]);
    }
}
