<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;

class Map
{
    private $twig;
    private $database;
    public function __construct(Twig $twig, PDO $database)
    {
        $this->twig = $twig;
        $this->database = $database;
    }

    public function run($post, $world)
    {
        World::register($this->database, $world);
        $data['villages'] = array_map(
            function(array $row) {return ['population' => intval($row['population'], 10),'isCapital' => 1===intval($row['is_capital'], 10), 'x' => intval($row['x'], 10), 'y' => intval($row['y'], 10), 'alliance' => $row['alliance_name'], 'village' => $row['village_name'], 'player' => $row['player_name']];},
            $this->database->query("SELECT x,y,village_name,player_name,alliance_name,is_capital,population FROM `$world`")->fetchAll(PDO::FETCH_ASSOC)
        );
        $this->twig->display('map.twig', $data);
    }
}
