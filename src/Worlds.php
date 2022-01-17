<?php

namespace De\Idrinth\Travian;

use Exception;
use PDO;

class Worlds
{
    private $twig;
    private $database;
    public function __construct(Twig $twig, PDO $database)
    {
        $this->twig = $twig;
        $this->database = $database;
    }
    public function run($post): void
    {
        $stmt = $this->database->query('SELECT * FROM world_updates');
        $context['worlds'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($context['worlds'] as &$world) {
            try {
                $stmt = $this->database->query('SELECT COUNT(DISTINCT player_id) as players,COUNT(DISTINCT village_id) AS villages,COUNT(DISTINCT alliance_id) AS alliances FROM `' . $world['world'] . '`');
                list($world['players'], $world['villages'], $world['alliances']) = $stmt->fetch(PDO::FETCH_NUM);
            } catch (Exception $ex) {
                $world['players'] = 0;
                $world['villages'] = 0;
                $world['alliances'] = 0;
            }
        }
        $this->twig->display('worlds.twig', $context);
    }
}
