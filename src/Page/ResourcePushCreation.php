<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use Exception;
use PDO;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class ResourcePushCreation
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
        $data['inputs'] = [
            'grain' => 0,
            'lumber' => 0,
            'iron' => 0,
            'resources' => 0,
            'clay' => 0,
        ];
        if (isset($post['x']) && isset($post['y']) && isset($post['player']) && isset($post['world']) && isset($post['resources']) && $post['resources'] >= 0 && isset($post['lumber']) && $post['lumber'] >= 0 && ($post['resources']+$post['lumber']+$post['clay']+$post['iron']+$post['crop'] > 0) && isset($post['time']) && isset($post['date'])) {
            try {
                $uuid = Uuid::uuid6();
                $stmt = $this->database->prepare(
                    "INSERT INTO resource_pushes (grain,lumber,clay,iron,resources, player, id, `key`, `x`, `y`, world, creator, arrival, alliance) "
                    . "VALUES (:grain,:lumber,:clay,:iron, :resources, :player, :id, :key, :x, :y, :world, :creator, :arrival, :alliance)"
                );
                if ($post['alliance_lock'] > 0) {
                    $stmt2 = $this->database->prepare("SELECT world FROM alliances WHERE aid=:aid");
                    $stmt2->execute([':aid' => $post['alliance_lock']]);
                    Assert::eq($stmt2->fetchColumn(), World::toWorld($post['world']), 'Alliance and entered world don\'t match');
                }
                $key = Uuid::uuid4();
                $stmt->execute([
                    ':id' => $uuid,
                    ':key' => $key,
                    ':world' => World::toWorld($post['world']),
                    ':x' => intval($post['x'], 10),
                    ':y' => intval($post['y'], 10),
                    ':arrival' => date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['time'])),
                    ':creator' => $_SESSION['id'] ?? 0,
                    ':alliance' => intval($post['alliance_lock'], 10),
                    ':player' => $post['player'] ?? '',
                    ':grain' => intval($post['grain'] ?? 0, 10),
                    ':iron' => intval($post['iron'] ?? 0, 10),
                    ':clay' => intval($post['clay'] ?? 0, 10),
                    ':lumber' => intval($post['lumber'] ?? 0, 10),
                    ':resources' => intval($post['resources'] ?? 0, 10),
                ]);
                header('Location: /resource-push/' . $uuid . '/' . $key, true, 303);
                return;
            } catch(Exception $e) {
                $data['error'] = $e->getMessage();
                $data['inputs'] = $post;
            }
        }
        $stmt = $this->database->prepare("SELECT alliances.* FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user");
        $stmt->execute([':user' => $_SESSION['id'] ?? 0]);
        $data['alliances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('res-push-creation.twig', $data);
    }
}
