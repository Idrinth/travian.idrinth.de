<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Point;
use De\Idrinth\Travian\Twig;
use DOMDocument;
use Throwable;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

class Delivery
{
    private static $townHall = [
        [24*3600],
        [23*3600 + 8*60 + 10],
        [22*3600 + 18*60 + 11],
        [21*3600 + 30*60 + 1],
        [20*3600 + 43*60 + 34],
        [19*3600 + 58*60 + 48],
        [19*3600 + 15*60 + 39],
        [18*3600 + 34*60 + 3],
        [17*3600 + 53*60 + 56],
        [17*3600 + 15*60 + 17, 43*3600 + 8*60 + 11],
        [16*3600 + 38*60 + 0, 41*3600 + 35*60 + 1],
        [16*3600 + 02*60 + 5, 40*3600 + 5*60 + 12],
        [15*3600 + 27*60 + 27, 38*3600 + 38*60 + 36],
        [14*3600 + 54*60 + 3, 37*3600 + 15*60 + 8],
        [14*3600 + 21*60 + 52, 35*3600 + 54*60 + 40],
        [13*3600 + 50*60 + 50, 34*3600 + 37*60 + 6],
        [13*3600 + 20*60 + 56, 33*3600 + 22*60 + 20],
        [12*3600 + 52*60 + 6, 32*3600 + 10*60 + 15],
        [12*3600 + 24*60 + 18, 31*3600 + 0*60 + 45],
        [11*3600 + 57*60 + 30, 29*3600 + 53*60 + 46],
     ];
    private static $celebrations = [
        'small' => [6400, 6650, 5940, 1340],
        'great' => [29700, 33250, 32000, 6700]
    ];
    private static $tribes = [
        'vid_1' => [16, 'romans'],
        'vid_2' => [12, 'teutons'],
        'vid_3' => [24, 'gauls'],
        'vid_7' => [20, 'huns'],
        'vid_6' => [16, 'egyptians'],
    ];
    private $twig;
    private $distance;
    public function __construct(Twig $twig, DistanceCalculator $distance)
    {
        $this->twig = $twig;
        $this->distance = $distance;
    }
    private function getTribe(string $tribeInput, DOMDocument $doc): array
    {
        if ($tribeInput === 'auto') {
            $tribeCheck = array_values(array_filter(explode(' ', $doc->getElementById('questmasterButton')->getAttribute('class')), function ($part) {
                return preg_match('/^vid_[0-9]/', $part);
            }))[0];
        } else {
            $tribeCheck = $tribeInput;
        }
        Assert::inArray($tribeCheck, array_keys(self::$tribes), 'Unknown tribe.');
        return self::$tribes[$tribeCheck];
    }
    private function getSpeed(\DOMDocument $doc): int
    {
        $scripts = $doc->getElementsByTagName('script');
        for ($i = 0; $i < $scripts->length; $i++) {
            if (preg_match('/Travian.Game.speed\s+=/', $scripts->item($i)->textContent)) {
                preg_match('/Travian.Game.speed\s=\s+([0-9]+);/', $scripts->item($i)->textContent, $matches);
                $speed = intval($matches[1], 10);
                Assert::greaterThanEq($speed, 1, 'Speed was below 1');
                Assert::lessThanEq($speed, 10, 'Speed was above 10');
                return $speed;
            }
        }
        throw new UnexpectedValueException('Couldn\'t find world speed');
    }
    private function getMapSize(\DOMDocument $doc): array
    {
        $scripts = $doc->getElementsByTagName('script');
        for ($i = 0; $i < $scripts->length; $i++) {
            if (preg_match('/window.TravianDefaults\s+=/', $scripts->item($i)->textContent)) {
                preg_match('/"width":([\-0-9]+),"height":([\-0-9]+)/', $scripts->item($i)->textContent, $matches);
                return [
                    'width' => intval($matches[1], 10),
                    'height' => intval($matches[2], 10),
                ];
            }
        }
        throw new UnexpectedValueException('Couldn\'t find map size');
    }
    private function mayTravelOverMapBorder(\DOMDocument $doc): bool
    {
        $scripts = $doc->getElementsByTagName('script');
        for ($i = 0; $i < $scripts->length; $i++) {
            if (preg_match('/"travelOverTheWorldEdge":true/', $scripts->item($i)->textContent)) {
                return true;
            }
        }
        return false;
    }
    private function getProduction(\DOMDocument $doc): array
    {
        $scripts = $doc->getElementsByTagName('script');
        for ($i = 0; $i < $scripts->length; $i++) {
            if (preg_match('/var\s+resources\s+=\s+/', $scripts->item($i)->textContent)) {
                preg_match('/"l1":\s+(-?[0-9]+),"l2":\s+(-?[0-9]+),"l3":\s+(-?[0-9]+),"l4":\s+(-?[0-9]+),"l5":\s+(-?[0-9]+)/', $scripts->item($i)->textContent, $matches);
                return [
                    'lumber' => intval($matches[1], 10),
                    'clay' => intval($matches[2], 10),
                    'iron' => intval($matches[3], 10),
                    'crop' => intval($matches[4], 10),
                ];
            }
        }
        throw new UnexpectedValueException('Couldn\'t find production');
    }
    private function getVillages(\DOMDocument $doc): array
    {
        $villages = [];
        $village = [];
        $divs = $doc->getElementById('sidebarBoxVillagelist')->getElementsByTagName('div');
        for ($i=0; $i < $divs->length; $i++) {
            if (in_array('villageList', explode(' ', $divs->item($i)->attributes->getNamedItem('class')->textContent), true)) {
                $as = $divs->item($i)->getElementsByTagName('a');
                for ($j=0; $j < $as->length; $j++) {
                    $span = $as->item($j)->nextSibling->nextSibling;
                    if (!in_array('active', explode(' ', $span->parentNode->attributes->getNamedItem('class')->textContent))) {
                        $villages[] = [
                            'name' => $span->attributes->getNamedItem('data-villagename')->textContent,
                            'x' => intval($span->attributes->getNamedItem('data-x')->textContent, 10),
                            'y' => intval($span->attributes->getNamedItem('data-y')->textContent, 10),
                        ];
                    } else {
                        $village = [
                            'name' => $span->attributes->getNamedItem('data-villagename')->textContent,
                            'x' => intval($span->attributes->getNamedItem('data-x')->textContent, 10),
                            'y' => intval($span->attributes->getNamedItem('data-y')->textContent, 10),
                        ];
                    }
                }
                return [$village, $villages];
            }
        }
        throw new UnexpectedValueException('No Village found');
    }
    private function calculateVillageResult(array $mapSize, bool $mayTravelOverWorldEdge, array $rootVillage, array $village, int $requiredTraders, array $inputs): array
    {
        $data['village'] = $village;
        $data['distance'] = $this->distance->distance(new Point($rootVillage['x'], $rootVillage['y']), new Point($village['x'], $village['y']), $mapSize['width'], $mapSize['height'], $mayTravelOverWorldEdge);
        $data['travelTime'] = ceil(3600 * $data['distance'] / $inputs['speed'] / $inputs['worldspeed']);
        $data['traders'] = 0;
        do {
            $data['traders']++;
            $total = $requiredTraders * (ceil(2 * $data['travelTime'] / 60) * 60) / $data['traders'];
        } while ($total > 3600 && $data['traders']<20);
        $data['minBetweenTrades'] = ceil(60 / $requiredTraders * $data['traders']);
        $data['lumber'] = $inputs['lumber'] > 0 ? floor($inputs['lumber'] / $requiredTraders * $data['traders']) : 0;
        $data['clay'] = $inputs['clay'] > 0 ? floor($inputs['clay'] / $requiredTraders * $data['traders']) : 0;
        $data['iron'] = $inputs['iron'] > 0 ? floor($inputs['iron'] / $requiredTraders * $data['traders']) : 0;
        $data['crop'] = $inputs['crop'] > 0 ? floor($inputs['crop'] / $requiredTraders * $data['traders']) : 0;
        return $data;
    }
    public function run(array $post): void
    {
        $data = [
            'inputs' => [
                'content' => $post['content'] ?? '',
                'tribe' => $post['tribe'] ?? 'auto',
                'town_hall' => min(20, max(0, intval($post['town_hall'] ?? 0))),
                'celebration' => $post['celebration'] ?? 'none',
                'only_crop' => 1 == ($post['only_crop'] ?? 0),
                'x' => intval($post['x']??0, 10),
                'y' => intval($post['y']??0, 10)
            ],
            'calculatedInputs' => [],
            'rawCalculatedInputs' => [],
            'results' => [],
        ];
        if ($data['inputs']['content']) {
            try {
                $doc = new DOMDocument();
                $doc->loadHTML($data['inputs']['content']);
                list($data['calculatedInputs']['speed'], $data['calculatedInputs']['tribe']) = $this->getTribe($data['inputs']['tribe'], $doc);
                $data['calculatedInputs']['capacity'] = intval($doc->getElementById('addRessourcesLink1')->textContent, 10);
                Assert::greaterThan($data['calculatedInputs']['capacity'], 0, 'Wrong page entered.');
                $data['rawCalculatedInputs'] = $this->getProduction($doc);
                $data['calculatedInputs'] += $data['rawCalculatedInputs'];
                $data['calculatedInputs']['worldspeed'] = $this->getSpeed($doc);
                if ($data['inputs']['celebration'] === 'small' && $data['inputs']['town_hall'] > 0) {
                    $data['calculatedInputs']['lumber'] = floor($data['calculatedInputs']['lumber'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][0] * self::$celebrations['small'][0]);
                    $data['calculatedInputs']['clay'] = floor($data['calculatedInputs']['clay'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][0] * self::$celebrations['small'][1]);
                    $data['calculatedInputs']['iron'] = floor($data['calculatedInputs']['iron'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][0] * self::$celebrations['small'][2]);
                    $data['calculatedInputs']['crop'] = floor($data['calculatedInputs']['crop'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][0] * self::$celebrations['small'][3]);
                } elseif ($data['inputs']['celebration'] === 'great' && $data['inputs']['town_hall'] > 9) {
                    $data['calculatedInputs']['lumber'] = floor($data['calculatedInputs']['lumber'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][1] * self::$celebrations['great'][0]);
                    $data['calculatedInputs']['clay'] = floor($data['calculatedInputs']['clay'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][1] * self::$celebrations['great'][1]);
                    $data['calculatedInputs']['iron'] = floor($data['calculatedInputs']['iron'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][1] * self::$celebrations['great'][2]);
                    $data['calculatedInputs']['crop'] = floor($data['calculatedInputs']['crop'] - 3600 / self::$townHall[$data['inputs']['town_hall'] - 1][1] * self::$celebrations['great'][3]);
                }
                if ($data['inputs']['only_crop']) {
                    $data['calculatedInputs']['lumber'] = 0;
                    $data['calculatedInputs']['clay'] = 0;
                    $data['calculatedInputs']['iron'] = 0;
                }
                $data['calculatedInputs']['total'] = 0
                        + ($data['calculatedInputs']['lumber'] < 0 ? 0 : $data['calculatedInputs']['lumber'])
                        + ($data['calculatedInputs']['clay'] < 0 ? 0 : $data['calculatedInputs']['clay'])
                        + ($data['calculatedInputs']['iron'] < 0 ? 0 : $data['calculatedInputs']['iron'])
                        + ($data['calculatedInputs']['crop'] < 0 ? 0 : $data['calculatedInputs']['crop']);
                $requiredTraders = ceil($data['calculatedInputs']['total'] / $data['calculatedInputs']['capacity']);
                list($data['calculatedInputs']['village'], $villages) = $this->getVillages($doc);
                if ($post['x']??0 !== 0 || $post['x']??0 !== 0) {
                    $villages[] = [
                        'name' => $data['translations']['additional_target'],
                        'x' => $data['inputs']['x'],
                        'y' => $data['inputs']['y']
                    ];
                }
                $mapSize = $this->getMapSize($doc);
                $mayTravelOverWorldEdge = $this->mayTravelOverMapBorder($doc);
                $data['results'] = [];
                foreach ($villages as $pos => $village) {
                    $data['results'][$pos] = $this->calculateVillageResult($mapSize, $mayTravelOverWorldEdge, $data['calculatedInputs']['village'], $village, $requiredTraders, $data['calculatedInputs']);
                }
                if (count($villages) === 0) {
                    $data['results'] = ['error' => 'Only a single village entered, can\'t send to itself.'];
                }
            } catch (Throwable $t) {
                $data['results'] = ['error' => 'Failed parsing your data: ' . $t->getMessage()];
            }
        }
        $this->twig->display('delivery.twig', $data);
    }
}
 