<?php

namespace De\Idrinth\Travian;

class TravelTime
{
    private static $speed = [];
    public function find(float $distance, float $shoes, float $map, int $duration, int $blindTime)
    {
        $result = [];
        foreach(self::$speed as $speed => $units) {
            for ($i=0; $i<=20; $i++) {
                if ($shoes !== 0 || $map !== 0) {
                    $time = $this->time($distance, $speed, 0, $i, $shoes, $map);
                    if ($time[0] >= $duration && $time[0] <= $duration + $blindTime) {
                        $result[] = [$speed, $i, $units, $time, 'with hero'];
                    }
                }
                $time = $this->time($post['distance'], $speed, 0, $i, 0, 0);
                if ($time[0] >= $duration && $time[0] <= $duration + $blindTime) {
                    $result[] = [$speed, $i, $units, $time, ''];
                }
            }
        }
        return $result;
    }
    public function time(float $distance, int $baseSpeed, float $speedBonus, int $tournamentSquareLevel, float $shoes, float $map): array
    {
        $to = (min(20, $distance) + max(0, $distance-20)/(1+0.2*$tournamentSquareLevel + $shoes))/$baseSpeed*3600/(1+$speedBonus);
        return [
            round($to),
            round($to * $map),
        ];
    }
}
