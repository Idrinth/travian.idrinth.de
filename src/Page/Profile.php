<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;

class Profile
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post): void
    {
        if (!isset($_SESSION['id']) || !$_SESSION['id']) {
            header('Location: /login', true, 303);
            return;
        }
        if (isset($post['delete-alliance'])) {
            $this->database
                ->prepare('DELETE FROM user_alliance WHERE user=:user AND alliance=:aid')
                ->execute([':user' => $_SESSION['id'], ':aid' => $post['delete-alliance']]);
            $stmt = $this->database->prepare("SELECT COUNT(*) FROM user_alliance WHERE rank IN('Creator', 'High Council') AND alliance=:aid");
            $stmt->execute([':aid' => $post['delete-alliance']]);
            if (($stmt->fetchColumn()?:0) <= 1) {
                $this->database->prepare("UPDATE user_alliance SET rank='High Council' WHERE alliance=:aid AND rank='Member' LIMIT 1");
                $stmt->execute([':aid' => $post['delete-alliance']]);
                $stmt = $this->database->prepare("SELECT COUNT(*) FROM user_alliance WHERE alliance=:aid AND rank='High Council'");
                $stmt->execute([':aid' => $post['delete-alliance']]);
                if (($stmt->fetchColumn()?:0) < 1) {
                    $this->database
                        ->prepare('DELETE FROM deff_calls WHERE alliance=:alliance')
                        ->execute([':alliance' => $post['delete-alliance']]);
                    $this->database
                        ->prepare('DELETE FROM hero WHERE alliance=:alliance')
                        ->execute([':alliance' => $post['delete-alliance']]);
                    $this->database
                        ->prepare('DELETE FROM alliances WHERE aid=:alliance')
                        ->execute([':alliance' => $post['delete-alliance']]);
                    $this->database
                        ->exec('DELETE FROM hero_updates WHERE hero NOT IN(SELECT aid FROM hero)');
                    $this->database
                        ->exec('DELETE FROM deff_call_supplies WHERE deff_call NOT IN(SELECT aid FROM deff_calls)');
                    $this->database
                        ->exec('DELETE FROM deff_call_supports WHERE deff_call NOT IN(SELECT aid FROM deff_calls)');
                    $this->database
                        ->exec('DELETE FROM user_deff_call WHERE deff_call NOT IN(SELECT aid FROM deff_calls)');
                }
            }
        } elseif (isset($post['delete-world'])) {
            $this->database
                ->prepare('DELETE FROM user_world WHERE user=:user AND aid=:aid')
                ->execute([':user' => $_SESSION['id'], ':aid' => $post['delete-world']]);
        } elseif(isset($post['world']) && isset($post['name'])) {
            $world = World::toWorld($post['world']);
            $stmt = $this->database->prepare('SELECT 1 FROM user_world WHERE user=:user AND world=:world');
            $stmt->execute([':user' => $_SESSION['id'], ':world' => $world]);
            if ($stmt->fetchColumn() == 1) {
                $this->database
                    ->prepare('UPDATE user_world SET name=:name WHERE user=:user AND world=:world')
                    ->execute([':user' => $_SESSION['id'], ':world' => $world, ':name' => $post['name']]);
            } else {
                $this->database
                    ->prepare('INSERT INTO user_world (name,user,world) VALUES (:name, :user, :world)')
                    ->execute([':user' => $_SESSION['id'], ':world' => $world, ':name' => $post['name']]);
            }
            World::register($this->database, $world);
        }
        $stmt = $this->database->prepare(
            "SELECT user_deff_call.advanced, deff_calls.arrival, deff_calls.key, deff_calls.id, deff_calls.world, deff_calls.x, deff_calls.y "
            . "FROM user_deff_call "
            . "INNER JOIN deff_calls "
            . "ON deff_calls.aid=user_deff_call.deff_call "
            . "AND user_deff_call.user=:user "
            . "AND deff_calls.deleted=0"
        );
        $stmt1 = $this->database->prepare(
            "SELECT alliances.name, alliances.world, alliances.id, alliances.aid, user_alliance.rank "
            . "FROM user_alliance "
            . "INNER JOIN alliances "
            . "ON alliances.aid=user_alliance.alliance "
            . "AND user_alliance.user=:user"
        );
        $stmt2 = $this->database->prepare(
            "SELECT * "
            . "FROM user_world "
            . "WHERE user=:user"
        );
        $stmt->execute([':user' => $_SESSION['id']]);
        $stmt1->execute([':user' => $_SESSION['id']]);
        $stmt2->execute([':user' => $_SESSION['id']]);
        $this->twig->display('profile.twig', [
            'deff_calls' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'alliances' => $stmt1->fetchAll(PDO::FETCH_ASSOC),
            'worlds' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }
}
