<?php

namespace De\Idrinth\Travian\API;

use PDO;

class Register
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run($post)
    {
        $apikey = getallheaders()['X-API-KEY']??getallheaders()['x-api-key']??'';
        if ($apikey !== $_ENV['API_KEY']) {
            header('Content-Type: application/json', true, 403);
            echo json_encode(['error' => 'API-Key "'.$apikey.'" Invalid']);
            return;
        }
        $stmt = $this->database->prepare('SELECT aid FROM alliances WHERE id=:id AND `key`=:key');
        $stmt->execute([':key' => $post['key'], ':id' => $post['id']]);
        $alliance = $stmt->fetchColumn();
        if ($alliance === false) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['error' => 'ID and KEY don\'t match any alliance.']);
            return;
        }
        $this->database
            ->prepare('INSERT INTO alliance_server (alliance,server_id) VALUES (:alliance,:server_id)')
            ->execute([':alliance' => $alliance, ':server_id' => $post['server_id']]);
        header('Content-Type: application/json', true, 200);
    }
}
