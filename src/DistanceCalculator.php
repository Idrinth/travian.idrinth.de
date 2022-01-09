<?php

namespace De\Idrinth\Travian;

class DistanceCalculator
{
    public function distance(Point $from, Point $to, int $width, int $height, bool $mayTravelOverWorldEdge): float
    {
        if (!$mayTravelOverWorldEdge) {
            return sqrt(($from->x - $to->x) * ($from->x - $to->x) + ($from->y - $to->y) * ($from->y - $to->y));
        }
        $possibleTos = [
            $to,
            new Point($to->x + $width, $to->y),
            new Point($to->x + $width, $to->y + $height),
            new Point($to->x, $to->y + $height),
            new Point($to->x - $width, $to->y),
            new Point($to->x - $width, $to->y + $height),
            new Point($to->x + $width, $to->y - $height),
            new Point($to->x, $to->y - $height),
            new Point($to->x - $width, $to->y - $height),
        ];
        $results=[];
        foreach ($possibleTos as $t) {
            $results[] = sqrt(($from->x - $t->x) * ($from->x - $t->x) + ($from->y - $t->y) * ($from->y - $t->y));
        }
        return min($results);
    }
}
