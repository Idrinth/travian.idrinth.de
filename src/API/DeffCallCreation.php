<?php

namespace De\Idrinth\Travian\API;

use De\Idrinth\Travian\World;
use InvalidArgumentException;
use PDO;
use Ramsey\Uuid\Uuid;
use Throwable;
use Webmozart\Assert\Assert;

class DeffCallCreation
{
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run($post)
    {
        $apikey = getallheaders()['X-API-KEY']??getallheaders()['x-api-key']??'';
        if ($apikey !== $_ENV['API_KEY']) {
            header('Content-Type: application/json', true, 403);
            echo 'API-Key "'.$apikey.'" Invalid';
            return;
        }
        $data = [];
        try {
            $uuid = Uuid::uuid6();
            $stmt = $this->database->prepare(
                "INSERT INTO deff_calls (created,anti,grain_info_hours,grain_production,grain,grain_storage,heroes, player, id, `key`, scouts, troops, `x`, `y`, world, creator, arrival, alliance,advanced_troop_data) "
                . "VALUES (:created,:anti,:grain_info_hours,:grain_production,:grain,:grain_storage,:heroes, :player, :id, :key, :scouts, :troops, :x, :y, :world, :creator, :arrival, :alliance,:advanced_troop_data)"
            );
            $stmt2 = $this->database->prepare('SELECT alliances.aid,world FROM alliances INNER JOIN alliance_server ON alliance_server.alliance=alliances.aid WHERE alliance_server.server_id=:id');
            $stmt2->execute([':id' => $post['server_id']]);
            $row = $stmt2->fetch(PDO::FETCH_NUM);
            if ($row === false) {
                header('Content-Type: application/json', true, 400);
                $data['error'] = 'Invalid Alliance-ID ' . $post['server_id'];
                echo json_encode($data);
                return;
            }
            list($post['alliance_lock'], $post['world']) = $row;
            World::register($this->database, $post['world']);
            Assert::greaterThan(strtotime($post['arrival']), time() + 3600, 'Defence is in the past or within the next hour.');
            Assert::greaterThan($post['heroes']+$post['scouts']+$post['troops'], 1, 'Defences without troops can\'t be created.');
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
            header('Content-Type: application/json', true, 200);
            $data['id'] = $uuid;
            $data['key'] = $key;
        } catch(InvalidArgumentException $e) {
            header('Content-Type: application/json', true, 400);
            $data['error'] = $e->getMessage();
            error_log("$e");
        } catch(Throwable $e) {
            header('Content-Type: application/json', true, 500);
            $data['error'] = $e->getMessage();
            error_log("$e");
        }
        echo json_encode($data);
    }
}
