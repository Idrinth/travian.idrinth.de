<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
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
    public function run($post, $world, $id = ''): void
    {
        World::register($this->database, $world);
        if ($id) {
            $stmt = $this->database->prepare('SELECT * FROM world_players WHERE id=:id AND world=:world');
            $stmt->execute([':id' => $id, ':world' => $world]);
            $stmt2 = $this->database->prepare('SELECT * FROM world_villages WHERE player=:id AND world=:world ORDER BY day ASC');
            $stmt2->execute([':id' => $id, ':world' => $world]);
            $villages = [];
            $population = [];
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $village) {
                $villages[$village['id']] = $villages[$village['id']] ?? $village;
                $villages[$village['id']]['name'] = $village['name'];
                $villages[$village['id']]['days'][$village['day']] = $village['population'];
                $population[$village['day']] = ($population[$village['day']]??0) + $village['population'];
            }
            foreach ($villages as &$village) {
                $data = [];
                foreach ($population as $day => $amount) {
                    $data[$day] = $village['days'][$day] ?? 0;
                }
                $village['days'] = array_values($data);
            }
            $this->twig->display('world-player.twig', [
                'world' => $world,
                'player' => $stmt->fetch(PDO::FETCH_ASSOC),
                'villages' => $villages,
                'population' => array_values($population),
                'days' => array_keys($population),
            ]);
            return;
        }
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
