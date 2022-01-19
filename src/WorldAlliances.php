<?php

namespace De\Idrinth\Travian;

use PDO;

class WorldAlliances
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run($post, $world): void
    {
        $stmt = $this->database->query("SELECT alliance_id AS id,alliance_name AS name,COUNT(DISTINCT player_id) AS players, SUM(population) AS population, COUNT(DISTINCT village_id) AS villages
FROM `$world`
WHERE alliance_id <> 0
GROUP BY alliance_id
ORDER BY population DESC");
        $this->twig->display('world-alliances.twig', [
            'world' => $world,
            'alliances' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }
}
