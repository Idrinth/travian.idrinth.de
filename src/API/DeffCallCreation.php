<?php

namespace De\Idrinth\Travian\API;

use De\Idrinth\Travian\World;
use Exception;
use PDO;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class DeffCallCreation
{
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run($post)
    {
        $apikey = getallheaders()['X-API-KEY']??'';
        header('Content-Type: application/json');
        if ($apikey !== $_ENV['API_KEY']) {
            header('', true, 403);
            return;
        }
        $data = [];
        try {
            $uuid = Uuid::uuid6();
            $stmt = $this->database->prepare(
                "INSERT INTO deff_calls (created,anti,grain_info_hours,grain_production,grain,grain_storage,heroes, player, id, `key`, scouts, troops, `x`, `y`, world, creator, arrival, alliance,advanced_troop_data) "
                . "VALUES (:created,:anti,:grain_info_hours,:grain_production,:grain,:grain_storage,:heroes, :player, :id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival, :alliance,:advanced_troop_data)"
            );
            $stmt2 = $this->database->prepare("SELECT aid,world FROM alliances WHERE id=:id");
            $stmt2->execute([':id' => $post['alliance']]);
            list($post['alliance_lock'], $post['world']) = $stmt->fetch(PDO::FETCH_NUM);
            World::register($this->database, $post['world']);
            Assert::greaterThan(strtotime($post['arrival']), time() + 3600, 'Defence is in the past or within the next hour.');
            $key = Uuid::uuid4();
            $stmt->execute([
                ':id' => $uuid,
                ':key' => $key,
                ':world' => $post['world'],
                ':x' => intval($post['x'], 10),
                ':y' => intval($post['y'], 10),
                ':scouts' => intval($post['scouts'], 10),
                ':troops' => intval($post['troops'], 10),
                ':heroes' => intval($post['heroes'], 10),
                ':arrival' => date('Y-m-d H:i:s', strtotime($post['arrival'])),
                ':created' => date('Y-m-d H:i:s'),
                ':creator' => $_SESSION['id'] ?? 0,
                ':alliance' => intval($post['alliance_lock'], 10),
                ':player' => $post['player'] ?? '',
                ':advanced_troop_data' => $post['advanced-troop-data'] ?? 0,
                ':grain' => $post['grain'] ?? 0,
                ':grain_storage' => $post['grain-storage'] ?? 0,
                ':grain_production' => $post['grain-production'] ?? 0,
                ':grain_info_hours' => $post['grain-info-hours'] ?? 0,
                ':anti' => $post['troop-ratio'] ?? 0,
            ]);
            $data['id'] = $uuid;
            $data['key'] = $key;
        } catch(Exception $e) {
            header('', true, 400);
            $data['error'] = $e->getMessage();
        }
        echo json_encode($data);
    }
}
