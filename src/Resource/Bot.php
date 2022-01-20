<?php

namespace De\Idrinth\Travian\Resource;

use De\Idrinth\Travian\World;
use PDO;

class Bot
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run($post, $world, $villages, $population)
    {
        World::register($this->database, $world);
        $stmt = $this->database->query("SELECT alliance_name,player_id AS id,player_name AS name, alliance_id, SUM(population) AS population, COUNT(DISTINCT village_id) AS villages,`x`,`y`
FROM `$world`
WHERE player_id NOT IN (1, 0)
GROUP BY player_id
ORDER BY population DESC");
        header('Content-Type: text/plain');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) AS $row) {
            if ($row['villages'] == $villages && $row['population'] >= $population) {
                echo $row['name'] . ',';
            }
        }
    }
}
