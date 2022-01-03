<?php

namespace De\Idrinth\Travian;

use Exception;
use PDO;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class DeffCallCreation
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
        if (isset($post['x']) && isset($post['y']) && isset($post['world']) && isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts'] > 0) && isset($post['time']) && isset($post['date'])) {
            $uuid = Uuid::uuid6();
            $stmt = $this->database->prepare(
                "INSERT INTO deff_calls (id, `key`, scouts, troops, `x`, `y`, world, creator, arrival, alliance) "
                . "VALUES (:id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival, :alliance)"
            );
            if (strpos($post['world'], 'https://') === 0) {
                $post['world'] = substr($post['world'], 8);
            }
            $post['world'] = explode('/', $post['world'])[0];
            try {
                Assert::regex($post['world'], '/^ts[0-9]+\.x[0-9]+\.[a-z]+\.travian\.com$/');
                $key = Uuid::uuid4();
                $stmt->execute([
                    ':id' => $uuid,
                    ':key' => $key,
                    ':world' => strtolower($post['world']),
                    ':x' => intval($post['x'], 10),
                    ':y' => intval($post['y'], 10),
                    ':scouts' => intval($post['scouts'], 10),
                    ':troops' => intval($post['troops'], 10),
                    ':arrival' => date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['time'])),
                    ':creator' => $_SESSION['id'] ?? 0,
                    ':alliance' => intval($post['alliance_lock'], 10),
                ]);
                header('Location: /deff-call/' . $uuid . '/' . $key, true, 303);
                return;
            } catch(Exception $e) {
                //someone messed up
            }
        }
        $stmt = $this->database->prepare("SELECT alliances.* FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user");
        $stmt->execute([':user' => $_SESSION['id'] ?? 0]);
        $data['alliances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('create-deff-call.twig');
    }
}
