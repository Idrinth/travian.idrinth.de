<?php

namespace De\Idrinth\Travian;

class TravelTime
{
    public function find(float $distance, int $shoes, int $map, int $duration, int $blindTime)
    {
        $result = [];
        foreach(Troops::SPEED as $unit => $speed) {
            for ($i=0; $i<=20; $i++) {
                $time = $this->time($distance, $speed, 0, $i, 0, 1);
                if ($time[0] >= $duration && $time[0] <= $duration + $blindTime) {
                    $result[$unit] = $result[$unit] ?? [$speed, $i, $unit, $time, ''];
                }
                if ($shoes !== 0 || $map !== 0) {
                    $time = $this->time($distance, $speed, 0, $i, 1 - $shoes/100, 1 - $map/100);
                    if ($time[0] >= $duration && $time[0] <= $duration + $blindTime) {
                        $result[$unit] = $result[$unit] ?? [$speed, $i, $unit, $time, 'with hero'];
                    }
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
