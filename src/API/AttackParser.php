<?php

namespace De\Idrinth\Travian\API;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Point;

class AttackParser
{
    private $time;
    private $distance;
    public function __construct(TravelTime $time, DistanceCalculator $distance)
    {
        $this->time = $time;
        $this->distance = $distance;
    }
    public function run($post)
    {
        $apikey = getallheaders()['X-API-KEY']??getallheaders()['x-api-key']??'';
        if ($apikey !== $_ENV['API_KEY']) {
            header('Content-Type: application/json', true, 403);
            echo 'API-Key "'.$apikey.'" Invalid';
            return;
        }
        $blindTime = explode(':', $post['blind_time']);
        $blindTime = $blindTime[0]*3600 + $blindTime[1]*60 + $blindTime[2];
        $duration = explode(':', $post['duration']);
        $duration = $duration[0]*3600 + $duration[1]*60 + $duration[2];
        $distance = $this->distance->distance(
            new Point(intval($post['fromX'], 10), intval($post['fromY'], 10)),
            new Point(intval($post['toX'], 10), intval($post['toY'], 10)),
            401,
            401,
            true
        );
        $output = [];
        foreach($this->time->find($distance, intval($post['shoes'], 10), intval($post['map'], 10), $duration, $blindTime) as $unit => $row) {
            $output[$row[0]] = $output[$row[0]] ?? [
                'start' => date('Y-m-d H:i:s', time()+$duration - $row[3][0]),
                'returned' => date('Y-m-d H:i:s', time()+$duration + $row[3][1]),
                'tournament_square' => $row[1],
                'speed' => $row[0],
                'units' => [],
            ];
            $output[$row[0]]['units'][] = $unit;
        }
        header('Content-Type: application/json', true, 200);
        echo json_encode(array_values($output));
    }
}
