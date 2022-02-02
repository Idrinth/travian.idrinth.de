<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Point;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Troops;
use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;

class OffPicker
{
    private $database;
    private $twig;
    private $distance;
    private $time;
    public function __construct(PDO $database, Twig $twig, TravelTime $time, DistanceCalculator $distance)
    {
        $this->database = $database;
        $this->twig = $twig;
        $this->time = $time;
        $this->distance = $distance;
    }
    public function run($post)
    {
        if (($_SESSION['id']??0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        $data = [];
        if (isset($post['world'])) {
            $stmt = $this->database->prepare("SELECT troops.*
FROM alliances
INNER JOIN user_alliance ON user_alliance.alliance=alliances.aid AND user_alliance.rank IN ('Planner','High Council','Creator')
LEFT JOIN user_alliance AS ua ON ua.alliance=alliances.aid
INNER JOIN troops ON ua.user=troops.user AND troops.world=alliances.world
WHERE alliances.world=:world AND user_alliance.user=:user AND troops.off>=:off AND troops.ram>=:ram AND troops.catapult>=:catapult");
            $stmt->execute([
                ':user' => $_SESSION['id'],
                ':world' => World::toWorld($post['world']),
                ':off' => max(1, $post['off']),
                ':ram' => $post['rams'],
                ':catapult' => $post['catapults'],
            ]);
            $data['offs'] = array_map(function ($row) use ($post) {
                $speed = 0;
                if ($post['catapults'] > 0) {
                    $speed = 3;
                } elseif ($post['rams'] > 0) {
                    $speed = 4;
                } else {
                    $speed = 1000;
                    foreach ([1,2,3,4,5,6] as $soldier) {
                        if ($row['soldier'.$soldier] > 0 && in_array($row['tribe'] . '_soldier' . $soldier, Troops::OFF, true)) {
                            $speed = min($speed, Troops::SPEED[$row['tribe'] . '_soldier' . $soldier]);
                        }
                    }
                }
                $distance = $this->distance->distance(new Point($row['x'], $row['y']), new Point($post['x'], $post['y']), 401, 401, true);
                return [
                    'x' => $row['x'],
                    'y' => $row['y'],
                    'name' => $row['name'],
                    'off' => $row['off'],
                    'ram' => $row['ram'],
                    'catapult' => $row['catapult'],
                    'tournament_square' => $row['tournament_square'],
                    'speed' => $speed,
                    'distance' => $distance,
                    'duration' => $this->time->time($distance, $speed, 0, intval($row['tournament_square']), 0, 0)[0],
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
            $data['inputs'] = $post;
            usort($data['offs'], function($row1, $row2) {
                if ($row1['duration'] > $row2['duration']) {
                    return 1;
                }
                if ($row1['duration'] < $row2['duration']) {
                    return -1;
                }
                if ($row1['distance'] > $row2['distance']) {
                    return 1;
                }
                if ($row1['distance'] < $row2['distance']) {
                    return -1;
                }
                return 0;
            });
        }
        $this->twig->display('off-picker.twig', $data);
    }
}
