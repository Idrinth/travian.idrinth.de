<?php

namespace De\Idrinth\Travian;

use PDO;

class WorldPlayers
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
        $stmt = $this->database->query("SELECT player_id AS id,player_name AS name, SUM(population) AS population, COUNT(DISTINCT village_id) AS villages
FROM `$world`
WHERE player_id NOT IN (1, 0)
GROUP BY player_id
ORDER BY population DESC");
        $this->twig->display('world-players.twig', [
            'world' => $world,
            'players' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }
}
