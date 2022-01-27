<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;

class InactiveSearch
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run($post)
    {
        $data = [];
        if (isset($post['world'])) {
            World::register($this->database, $post['world']);
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
WHERE (a.id=1 OR a.population<=b.population+:growth) AND SQRT((world_villages.x-:x)*(world_villages.x-:x) + (world_villages.y-:y)*(world_villages.y-:y)) < :distance');
            $stmt->execute([
                ':world' => $post['world'],
                ':x' => $post['x'],
                ':y' => $post['y'],
                ':distance' => $post['distance'],
                ':today' => date('Y-m-d'),
                ':previous' => date('Y-m-d', strtotime('now -'.intval($post['per_days'], 10).'days')),
                ':growth' => intval($post['max_growth'], 10)
            ]);
            $data['inactives'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $data['worlds'] = $this->database
            ->query('SELECT DISTINCT world FROM world_villages')
            ->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('inactive-search.twig', $data);
    }
}
