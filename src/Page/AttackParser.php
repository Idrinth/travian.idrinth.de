<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Twig;
use DOMDocument;
use DOMElement;
use PDO;
use UI\Point;
use UnexpectedValueException;

class AttackParser
{
    private $twig;
    private $time;
    private $database;
    private $distance;
    public function __construct(PDO $database, Twig $twig, TravelTime $time, DistanceCalculator $distance)
    {
        $this->twig = $twig;
        $this->time = $time;
        $this->distance = $distance;
        $this->database = $database;
    }
    private function getMapSize(DOMDocument $doc): array
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
    private function mayTravelOverMapBorder(DOMDocument $doc): bool
    {
        $scripts = $doc->getElementsByTagName('script');
        for ($i = 0; $i < $scripts->length; $i++) {
            if (preg_match('/"travelOverTheWorldEdge":true/', $scripts->item($i)->textContent)) {
                return true;
            }
        }
        return false;
    }
    private function getVillage(DOMDocument $doc): array
    {
        $divs = $doc->getElementById('sidebarBoxVillagelist')->getElementsByTagName('div');
        for ($i=0; $i < $divs->length; $i++) {
            if (in_array('villageList', explode(' ', $divs->item($i)->attributes->getNamedItem('class')->textContent), true)) {
                $as = $divs->item($i)->getElementsByTagName('a');
                for ($j=0; $j < $as->length; $j++) {
                    $span = $as->item($j)->nextSibling->nextSibling;
                    if (in_array('active', explode(' ', $span->parentNode->attributes->getNamedItem('class')->textContent))) {
                        return [
                            'name' => $span->attributes->getNamedItem('data-villagename')->textContent,
                            'x' => intval($span->attributes->getNamedItem('data-x')->textContent, 10),
                            'y' => intval($span->attributes->getNamedItem('data-y')->textContent, 10),
                        ];
                    }
                }
            }
        }
        throw new UnexpectedValueException('No Village found');
    }
    private function getCoords(DOMElement $table): array
    {
        for ($j = 0; $j < $table->childNodes->length; $j++) {
            $tbody = $table->childNodes->item($j);
            if ($tbody->localName === 'tbody') {
                if ($tbody->attributes->getNamedItem('class') && $tbody->attributes->getNamedItem('class')->textContent === 'units') {
                    for ($k = 0; $k < $tbody->childNodes->length; $k++) {
                        $tr = $tbody->childNodes->item($k);
                        if ($tr->localName === 'tr') {
                            for ($l = 0; $l < $tr->childNodes->length; $l++) {
                                $th = $tr->childNodes->item($l);
                                if ($th->localName === 'th') {
                                    if ($th->attributes->getNamedItem('class')->textContent === 'coords') {
                                        return explode('|', (preg_replace('#[^0-9|\-]#', '', str_replace('−', '-', $th->textContent))));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    private function getDuration(DOMElement $table): int
    {
        for ($j = 0; $j < $table->childNodes->length; $j++) {
            $tbody = $table->childNodes->item($j);
            if ($tbody->attributes->getNamedItem('class') && $tbody->attributes->getNamedItem('class')->textContent === 'infos') {
                for ($k = 0; $k < $tbody->childNodes->length; $k++) {
                    $tr = $tbody->childNodes->item($k);
                    if ($tr->localName === 'tr') {
                        for ($l = 0; $l < $tr->childNodes->length; $l++) {
                            $td = $tr->childNodes->item($l);
                            if ($td->localName === 'td') {
                                for ($m = 0; $m < $td->childNodes->length; $m++) {
                                    $div = $td->childNodes->item($m);
                                    if ($div->localName === 'div' && $div->attributes->getNamedItem('class')->textContent === 'in') {
                                        for ($n = 0; $n < $div->childNodes->length; $n++) {
                                            $span = $div->childNodes->item($n);
                                            if ($span->localName === 'span') {
                                                return intval($span->attributes->getNamedItem('value')->textContent, 10);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }        
    }
    public function run($post)
    {
        $data = [];
        if (isset($post['source']) && $post['source']) {
            $doc = new DOMDocument();
            $doc->loadHTML($post['source']);
            $blindTime = explode(':', $post['blind_time']);
            $blindTime = $blindTime[0]*3600 + $blindTime[1]*60 + $blindTime[2];
            $mayTravelOverBorder = $this->mayTravelOverMapBorder($doc);
            $data['size'] = $this->getMapSize($doc);
            $village = $this->getVillage($doc);
            $tables = $doc->getElementsByTagName('table');
            for ($i = 0; $i < $tables->length; $i++) {
                if (in_array('inAttack', explode(' ', $tables->item($i)->attributes->getNamedItem('class')->textContent))) {
                    $coords = $this->getCoords($tables->item($i));
                    $duration = $this->getDuration($tables->item($i));
                    $distance = $this->distance->distance(new Point($coords[0], $coords[1]), new Point($village['x'], $village['y']), $data['size']['width'], $data['size']['height'], $mayTravelOverBorder);
                    $row = $this->time->find($distance, 0, 0, $duration, $blindTime);
                    $data['attacks'][] = [
                        'from' => [
                            'x' => $coords[0],
                            'y' => $coords[1],
                        ],
                        'to' => $village,
                        'distance' => $distance,
                        'units' => $row,
                        'date' => date('Y-m-d', time()+$duration -1),
                        'time' => date('H:i:s', time()+$duration -1),
                    ];
                }
            }
            $nodes = $doc->getElementById('villageName')->parentNode->childNodes;
            for ($i = 0; $i < $nodes->length; $i++) {
                if ($nodes->item($i)->localName === 'div') {
                    $data['player'] = $nodes->item($i)->textContent;
                    break;
                }
            }
        }
        $stmt = $this->database->prepare("SELECT alliances.* FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user");
        $stmt->execute([':user' => $_SESSION['id'] ?? 0]);
        $data['alliances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->twig->display('attack-parser.twig', $data);
    }
}
