<?php

namespace De\Idrinth\Travian\API;

use InvalidArgumentException;
use PDO;
use Ramsey\Uuid\Uuid;
use Throwable;
use function GuzzleHttp\json_encode;

class ResourcePushCreation
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post): void
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
                "INSERT INTO resource_pushes (grain,lumber,clay,iron,resources, player, id, `key`, `x`, `y`, world, creator, arrival, alliance) "
                . "VALUES (:grain,:lumber,:clay,:iron, :resources, :player, :id, :key, :x, :y, :world, :creator, :arrival, :alliance)"
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
            $key = Uuid::uuid4();
            $stmt->execute([
                ':id' => $uuid,
                ':key' => $key,
                ':world' => $post['world'],
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
            $data['id'] = $uuid;
            $data['key'] = $key;
        } catch(InvalidArgumentException $e) {
            header('Content-Type: application/json', true, 403);
            $data['error'] = $e->getMessage();
        } catch(Throwable $e) {
            header('Content-Type: application/json', true, 500);
            $data['error'] = $e->getMessage();
        }
        echo json_encode($data);
    }
}
