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
        $data['inputs'] = [
            'scouts' => 0,
            'troops' => 0,
            'heroes' => 0,
        ];
        if (isset($post['x']) && isset($post['y']) && isset($post['player']) && isset($post['world']) && isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts']+$post['heroes'] > 0) && isset($post['time']) && isset($post['date'])) {
            try {
                $uuid = Uuid::uuid6();
                $stmt = $this->database->prepare(
                    "INSERT INTO deff_calls (grain,grain_storage,world_width,world_height,heroes, player, id, `key`, scouts, troops, `x`, `y`, world, creator, arrival, alliance,advanced_troop_data) "
                    . "VALUES (:grain,:grain_storage,:world_width,:world_height,:heroes, :player, :id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival, :alliance,:advanced_troop_data)"
                );
                if (strpos($post['world'], 'https://') === 0) {
                    $post['world'] = substr($post['world'], 8);
                }
                $post['world'] = explode('/', $post['world'])[0];
                Assert::regex($post['world'], '/^ts[0-9]+\.x[0-9]+\.[a-z]+\.travian\.com$/', 'World format is wrong');
                if ($post['alliance_lock'] > 0) {
                    $stmt2 = $this->database->prepare("SELECT world FROM alliances WHERE aid=:aid");
                    $stmt2->execute([':aid' => $post['alliance_lock']]);
                    Assert::eq($stmt2->fetchColumn(), $post['world'], 'Alliance and entered world don\'t match');
                }
                $key = Uuid::uuid4();
                $stmt->execute([
                    ':id' => $uuid,
                    ':key' => $key,
                    ':world' => strtolower($post['world']),
                    ':x' => intval($post['x'], 10),
                    ':y' => intval($post['y'], 10),
                    ':scouts' => intval($post['scouts'], 10),
                    ':troops' => intval($post['troops'], 10),
                    ':heroes' => intval($post['heroes'], 10),
                    ':arrival' => date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['time'])),
                    ':creator' => $_SESSION['id'] ?? 0,
                    ':alliance' => intval($post['alliance_lock'], 10),
                    ':player' => $post['player'] ?? '',
                    ':advanced_troop_data' => $post['advanced_troop_data'] ?? 0,
                    ':world_width' => $post['world_width'] ?? 401,
                    ':world_height' => $post['world_height'] ?? 401,
                    ':grain' => $post['grain'] ?? 0,
                    ':grain_storage' => $post['grain_storage'] ?? 800,
                ]);
                header('Location: /deff-call/' . $uuid . '/' . $key, true, 303);
                return;
            } catch(Exception $e) {
                $data['error'] = $e->getMessage();
                $data['inputs'] = $post;
            }
        }
        $stmt = $this->database->prepare("SELECT alliances.* FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user");
        $stmt->execute([':user' => $_SESSION['id'] ?? 0]);
        $data['alliances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('create-deff-call.twig', $data);
    }
}
