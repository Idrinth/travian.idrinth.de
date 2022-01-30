<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;
use De\Idrinth\Travian\Point;

class InactiveSearch
{
    private $database;
    private $twig;
    private $distance;
    public function __construct(PDO $database, Twig $twig, DistanceCalculator $distance)
    {
        $this->database = $database;
        $this->twig = $twig;
        $this->distance = $distance;
    }
    public function run($post)
    {
        $data = [
            'inputs' => [
                'x' => 0,
                'y' => 0,
                'distance' => 20,
                'max_growth' => 5,
                'per_days' => 2,
            ]
        ];
        if (isset($post['world'])) {
            World::register($this->database, $post['world']);
            $stmt = $this->database->prepare('SELECT DISTINCT 1 FROM world_villages WHERE day=:day');
            $stmt->execute(['day' => date('Y-m-d')]);
            $today = date('Y-m-d');
            if (false === $stmt->fetch()) {
                $today = date('Y-m-d', strtotime("$today -1day"));
            }
            $stmt = $this->database->prepare('SELECT a.population - b.population AS growth,a.name AS player,world_villages.x,world_villages.y,world_villages.population,world_villages.world,world_villages.name AS village FROM (
	SELECT world_players.id,world_players.name,SUM(IFNULL(world_villages.population, 0)) AS population
	FROM world_villages
	INNER JOIN world_players ON world_players.id=world_villages.player AND world_players.world=world_villages.world AND :today BETWEEN world_players.`from` AND world_players.`until`
	WHERE world_villages.world=:world AND world_villages.day=:today
	GROUP BY world_players.id
) AS a
LEFT JOIN (
	SELECT world_players.id,SUM(IFNULL(world_villages.population, 0)) AS population
	FROM world_villages
	INNER JOIN world_players ON world_players.id=world_villages.player AND world_players.world=world_villages.world AND :previous BETWEEN world_players.`from` AND world_players.`until`
	WHERE world_villages.world=:world AND world_villages.day=:previous
	GROUP BY world_players.id
) AS b ON b.id=a.id
INNER JOIN world_villages ON world_villages.world=:world AND world_villages.player=a.id AND world_villages.day=:today
WHERE ' . (isset($post['no_natars']) && $post['no_natars'] == 1 ? 'a.id<>1 AND ' : 'a.id=1 OR ' ) . 'a.population<=b.population+:growth');
            $stmt->execute([
                ':world' => $post['world'],
                ':today' => $today,
                ':previous' => date('Y-m-d', strtotime("$today -1{$post['per_days']}ays")),
                ':growth' => intval($post['max_growth'], 10)
            ]);
            $data['inactives'] = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $distance = $this->distance->distance(new Point(intval($post['x'], 10), intval($post['y'], 10)), new Point(intval($row['x'], 10), intval($row['y'], 10)), 401, 401, true);
                if ($distance <= $post['distance']) {
                    $data['inactives'][] = $row + ['distance' => $distance];
                }
            }
            $data['inputs'] = $post;
        }
        $data['worlds'] = $this->database
            ->query('SELECT DISTINCT world FROM world_villages')
            ->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('inactive-search.twig', $data);
    }
}
