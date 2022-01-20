<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
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
                    "INSERT INTO deff_calls (created,anti,grain_info_hours,grain_production,grain,grain_storage,heroes, player, id, `key`, scouts, troops, `x`, `y`, world, creator, arrival, alliance,advanced_troop_data) "
                    . "VALUES (:created,:anti,:grain_info_hours,:grain_production,:grain,:grain_storage,:heroes, :player, :id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival, :alliance,:advanced_troop_data)"
                );
                if ($post['alliance_lock'] > 0) {
                    $stmt2 = $this->database->prepare("SELECT world FROM alliances WHERE aid=:aid");
                    $stmt2->execute([':aid' => $post['alliance_lock']]);
                    Assert::eq($stmt2->fetchColumn(), World::toWorld($post['world']), 'Alliance and entered world don\'t match');
                }
                Assert::greaterThan(strtotime($post['date'] . ' ' . $post['time']), time() + 3600, 'Defence is in the past or within the next hour.');
                $key = Uuid::uuid4();
                $stmt->execute([
                    ':id' => $uuid,
                    ':key' => $key,
                    ':world' => World::toWorld($post['world']),
                    ':x' => intval($post['x'], 10),
                    ':y' => intval($post['y'], 10),
                    ':scouts' => intval($post['scouts'], 10),
                    ':troops' => intval($post['troops'], 10),
                    ':heroes' => intval($post['heroes'], 10),
                    ':arrival' => date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['time'])),
                    ':created' => date('Y-m-d H:i:s'),
                    ':creator' => $_SESSION['id'] ?? 0,
                    ':alliance' => intval($post['alliance_lock'], 10),
                    ':player' => $post['player'] ?? '',
                    ':advanced_troop_data' => $post['advanced_troop_data'] ?? 0,
                    ':grain' => $post['grain'] ?? 0,
                    ':grain_storage' => $post['grain_storage'] ?? 0,
                    ':grain_production' => $post['grain_production'] ?? 0,
                    ':grain_info_hours' => $post['grain_info_hours'] ?? 0,
                    ':anti' => $post['anti'] ?? 0,
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
