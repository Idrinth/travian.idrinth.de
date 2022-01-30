<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Troops;
use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use DOMDocument;
use PDO;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

class TroopTool
{
    private $database;
    private $twig;
    
    private static $tribes = [
        'vid_1' => 'roman',
        'vid_2' => 'teuton',
        'vid_3' => 'gaul',
        'vid_7' => 'hun',
        'vid_6' => 'egyptian',
    ];
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    private function updateStatistics()
    {        
        $stmt = $this->database->prepare("SELECT * FROM troops WHERE user=:user");
        $stmt->execute([':user' => $_SESSION['id']]);
        $worlds = [];
        $deff = [];
        $off = [];
        $multi = [];
        $scouts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as  $row) {
            $worlds[$row['world']] = $row['world'];
            $deff[$row['world']] = $deff[$row['world']] ?? 0;
            $off[$row['world']] = $off[$row['world']] ?? 0;
            $scouts[$row['world']] = $scouts[$row['world']] ?? 0;
            $multi[$row['world']] = $multi[$row['world']] ?? 0;
            $voff = 0;
            $vdeff = 0;
            if ($row['tribe'] === 'roman') {
                $deff[$row['world']] += $row['soldier1'] * Troops::CORN['roman_soldier1'];
                $deff[$row['world']] += $row['soldier2'] * Troops::CORN['roman_soldier2'];
                $off[$row['world']] += $row['soldier3'] * Troops::CORN['roman_soldier3'];
                $scouts[$row['world']] += $row['soldier4'] * Troops::CORN['roman_soldier4'];
                $off[$row['world']] += $row['soldier5'] * Troops::CORN['roman_soldier5'];
                $multi[$row['world']] += $row['soldier6'] * Troops::CORN['roman_soldier6'];
                $off[$row['world']] += $row['ram'] * Troops::CORN['roman_ram'];
                $off[$row['world']] += $row['catapult'] * Troops::CORN['roman_catapult'];
                $voff += $row['soldier6'] * Troops::CORN['roman_soldier6'] + $row['soldier3'] * Troops::CORN['roman_soldier3'];
                $vdeff += $row['soldier1'] * Troops::CORN['roman_soldier1'] + $row['soldier2'] * Troops::CORN['roman_soldier2'] + $row['soldier6'] * Troops::CORN['roman_soldier6'];
            } elseif ($row['tribe'] === 'gaul') {
                $deff[$row['world']] += $row['soldier1'] * Troops::CORN['gaul_soldier1'];
                $off[$row['world']] += $row['soldier2'] * Troops::CORN['gaul_soldier2'];
                $scouts[$row['world']] += $row['soldier3'] * Troops::CORN['gaul_soldier3'];
                $off[$row['world']] += $row['soldier4'] * Troops::CORN['gaul_soldier4'];
                $deff[$row['world']] += $row['soldier5'] * Troops::CORN['gaul_soldier5'];
                $multi[$row['world']] += $row['soldier6'] * Troops::CORN['gaul_soldier6'];
                $off[$row['world']] += $row['ram'] * Troops::CORN['gaul_ram'];
                $off[$row['world']] += $row['catapult'] * Troops::CORN['gaul_catapult'];
                $voff += $row['soldier2'] * Troops::CORN['gaul_soldier2'] + $row['soldier4'] * Troops::CORN['gaul_soldier4'] + $row['soldier6'] * Troops::CORN['gaul_soldier6'];
                $vdeff += $row['soldier1'] * Troops::CORN['gaul_soldier1'] + $row['soldier5'] * Troops::CORN['gaul_soldier5'] + $row['soldier6'] * Troops::CORN['gaul_soldier6'];
            } elseif ($row['tribe'] === 'teuton') {
                $off[$row['world']] += $row['soldier1'] * Troops::CORN['teuton_soldier1'];
                $deff[$row['world']] += $row['soldier2'] * Troops::CORN['teuton_soldier2'];
                $off[$row['world']] += $row['soldier3'] * Troops::CORN['teuton_soldier3'];
                $scouts[$row['world']] += $row['soldier4'] * Troops::CORN['teuton_soldier4'];
                $deff[$row['world']] += $row['soldier5'] * Troops::CORN['teuton_soldier5'];
                $off[$row['world']] += $row['soldier6'] * Troops::CORN['teuton_soldier6'];
                $off[$row['world']] += $row['ram'] * Troops::CORN['teuton_ram'];
                $off[$row['world']] += $row['catapult'] * Troops::CORN['teuton_catapult'];
                $voff += $row['soldier1'] * Troops::CORN['teuton_soldier1'] + $row['soldier3'] * Troops::CORN['teuton_soldier3'] + $row['soldier6'] * Troops::CORN['teuton_soldier6'];
                $vdeff += $row['soldier2'] * Troops::CORN['teuton_soldier2'] + $row['soldier5'] * Troops::CORN['teuton_soldier5'];
            } elseif ($row['tribe'] === 'egyptian') {
                $deff[$row['world']] += $row['soldier1'] * Troops::CORN['egyptian_soldier1'];
                $deff[$row['world']] += $row['soldier2'] * Troops::CORN['egyptian_soldier2'];
                $off[$row['world']] += $row['soldier3'] * Troops::CORN['egyptian_soldier3'];
                $scouts[$row['world']] += $row['soldier4'] * Troops::CORN['egyptian_soldier4'];
                $deff[$row['world']] += $row['soldier5'] * Troops::CORN['egyptian_soldier5'];
                $multi[$row['world']] += $row['soldier6'] * Troops::CORN['egyptian_soldier6'];
                $off[$row['world']] += $row['ram'] * Troops::CORN['egyptian_ram'];
                $off[$row['world']] += $row['catapult'] * Troops::CORN['egyptian_catapult'];
                $vdeff = $row['soldier1'] * Troops::CORN['egyptian_soldier1'] + $row['soldier2'] * Troops::CORN['egyptian_soldier2'] + $row['soldier5'] * Troops::CORN['egyptian_soldier5'] + $row['soldier6'] * Troops::CORN['egyptian_soldier6'];
                $voff = $row['soldier3'] * Troops::CORN['egyptian_soldier3'] + $row['soldier6'] * Troops::CORN['egyptian_soldier6'];
            } elseif ($row['tribe'] === 'hun') {
                $multi[$row['world']] += $row['soldier1'] * Troops::CORN['hun_soldier1'];
                $off[$row['world']] += $row['soldier2'] * Troops::CORN['hun_soldier2'];
                $scouts[$row['world']] += $row['soldier3'] * Troops::CORN['hun_soldier3'];
                $off[$row['world']] += $row['soldier4'] * Troops::CORN['hun_soldier4'];
                $multi[$row['world']] += $row['soldier5'] * Troops::CORN['hun_soldier5'];
                $off[$row['world']] += $row['soldier6'] * Troops::CORN['hun_soldier6'];
                $off[$row['world']] += $row['ram'] * Troops::CORN['hun_ram'];
                $off[$row['world']] += $row['catapult'] * Troops::CORN['hun_catapult'];
                $voff += $row['soldier1'] * Troops::CORN['hun_soldier1'] + $row['soldier2'] * Troops::CORN['hun_soldier2'] + $row['soldier4'] * Troops::CORN['hun_soldier4'] + $row['soldier5'] * Troops::CORN['hun_soldier5'] + $row['soldier6'] * Troops::CORN['hun_soldier6'];
                $vdeff += $row['soldier1'] * Troops::CORN['hun_soldier1'] + $row['soldier5'] * Troops::CORN['hun_soldier5'];
            }
            $this->database
                ->prepare('UPDATE troops SET off=:off,deff=:deff WHERE aid=:aid')
                ->execute([':aid' => $row['aid'], ':off' => $voff, ':deff' => $vdeff]);
        }
        $stmt = $this->database->prepare("SELECT aid,world FROM troop_updates WHERE user=:user AND date=:today");
        $stmt->execute([':user' => $_SESSION['id'], ':today' => date('Y-m-d')]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($worlds[$row['world']])) {
                $this->database
                    ->prepare('UPDATE troop_updates SET offensive=:off,multipurpose=:multi, defensive=:deff, scouts=:scouts WHERE aid=:aid')
                    ->execute([':aid' => $row['aid'], ':off' => $off[$row['world']], ':multi' => $multi[$row['world']], ':deff' => $deff[$row['world']], ':scouts' => $scouts[$row['world']]]);
                unset($worlds[$row['world']]);
            }
        }
        foreach ($worlds as $world) {
            $this->database
                ->prepare('INSERT INTO troop_updates (multipurpose,user, world, offensive, defensive, scouts, `date`) VALUES (:multi, :id, :world, :off, :deff, :scouts, :date)')
                ->execute([':date' => date('Y-m-d'),':id' => $_SESSION['id'], ':world' => $world, ':multi' => $multi[$world]??0, ':off' => $off[$world]??0, ':deff' => $deff[$world]??0, ':scouts' => $scouts[$world]??0]);
        }
    }
    public function run(array $post, $id = 0): void
    {
        if (($_SESSION['id'] ?? 0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        if ($id) {
            $stmt = $this->database->prepare("SELECT DISTINCT troops.*, user_alliance.user
FROM user_alliance
INNER JOIN alliances ON user_alliance.alliance=alliances.aid
INNER JOIN troops ON troops.world=alliances.world
INNER JOIN user_alliance AS ua2 ON ua2.alliance=user_alliance.alliance AND troops.user=ua2.user
WHERE troops.user=:id AND user_alliance.rank IN('High Council', 'Creator', 'Planner') AND user_alliance.user=:id2
ORDER BY troops.tribe DESC, troops.name ASC");
            $stmt->execute([':id' => $id, ':id2' => $_SESSION['id']]);
            $troopsData = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $troopsData[$row['world']] = $troopsData[$row['world']] ?? [];
                $troopsData[$row['world']][$row['tribe']] = $troopsData[$row['world']][$row['tribe']] ?? [];
                $troopsData[$row['world']][$row['tribe']][] = $row;
            }
            $stmt = $this->database->prepare("SELECT * FROM troop_updates WHERE user=:id ORDER BY world DESC, `date` ASC");
            $stmt->execute([':id' => $_SESSION['id']]);
            $charts = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $charts[$row['world']] = $charts[$row['world']] ?? [
                    'offence' => [],
                    'defence' => [],
                    'multi' => [],
                    'scouts' => [],
                    'labels' => [],
                ];
                $charts[$row['world']]['offence'][] = intval($row['offensive'], 10);
                $charts[$row['world']]['defence'][] = intval($row['defensive'], 10);
                $charts[$row['world']]['multi'][] = intval($row['multipurpose'], 10);
                $charts[$row['world']]['scouts'][] = intval($row['scouts'], 10);
                $charts[$row['world']]['labels'][] = $row['date'];
            }
            $this->twig->display('troop-tool-view.twig', [
                'troops' => $troopsData,
                'charts' => $charts
            ]);
            return;
        } elseif (isset($post['aid']) && isset($post['type']) && $post['type']==='delete') {
            $this->database
                ->prepare("DELETE FROM troops WHERE user=:id AND aid=:aid")
                ->execute([':id' => $_SESSION['id'], ':aid' => $post['aid']]);
        } elseif (isset($post['world']) && isset($post['tribe']) && isset($post['name']) && isset($post['x']) && isset($post['y'])) {
            $stmt = $this->database->prepare("SELECT 1 FROM troops WHERE user=:user AND world=:world AND x=:x AND y=:y");
            $stmt->execute([
                ':user' => $_SESSION['id'],
                ':world' => World::toWorld($post['world']),
                ':y' => $post['y'],
                ':x' => $post['x'],
            ]);
            if ($stmt->fetchColumn() === false) {
                $this->database
                    ->prepare("INSERT INTO troops(user,world,x,y,name, tribe) VALUES (:user,:world,:x,:y,:name,:tribe)")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':world' => World::toWorld($post['world']),
                        ':y' => $post['y'],
                        ':x' => $post['x'],
                        ':name' => $post['name'],
                        ':tribe' => $post['tribe'],
                    ]);
            }
        } elseif (isset($post['soldier1']) && is_array($post['soldier1'])) {
            foreach ($post['soldier1'] as $aid => $data) {
                $this->database
                    ->prepare("UPDATE troops "
                        . "SET tournament_square=:tournament_square,soldier1=:soldier1, soldier2=:soldier2, soldier3=:soldier3, soldier4=:soldier4, soldier5=:soldier5, soldier6=:soldier6, ram=:ram, catapult=:catapult, settler=:settler, chief=:chief, hero=:hero "
                        . "WHERE aid=:aid AND user=:user")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':aid' => $aid,
                        ':soldier1' => $post['soldier1'][$aid] ?? 0,
                        ':soldier2' => $post['soldier2'][$aid] ?? 0,
                        ':soldier3' => $post['soldier3'][$aid] ?? 0,
                        ':soldier4' => $post['soldier4'][$aid] ?? 0,
                        ':soldier5' => $post['soldier5'][$aid] ?? 0,
                        ':soldier6' => $post['soldier6'][$aid] ?? 0,
                        ':ram' => $post['ram'][$aid] ?? 0,
                        ':catapult' => $post['catapult'][$aid] ?? 0,
                        ':settler' => $post['settler'][$aid] ?? 0,
                        ':chief' => $post['chief'][$aid] ?? 0,
                        ':hero' => $post['hero'][$aid] ?? 0,
                        ':tournament_square' => $post['tournament_square'][$aid] ?? 0,
                    ]);
            }
            $this->updateStatistics();
        } elseif (isset($post['source']) && $post['source']) {
            $doc = new DOMDocument();
            if (true === $doc->loadHTML($post['source'])) {
                $villages = $this->getVillages($doc);
                $tribe = $this->getTribe($doc);
                foreach ($villages as $village) {
                    $stmt = $this->database->prepare("SELECT 1 FROM troops WHERE user=:user AND world=:world AND x=:x AND y=:y");
                    $stmt->execute([
                        ':user' => $_SESSION['id'],
                        ':world' => World::toWorld($post['world']),
                        ':y' => $village['y'],
                        ':x' => $village['x'],
                    ]);
                    if ($stmt->fetchColumn() === false) {
                        $this->database
                            ->prepare("INSERT INTO troops(user,world,x,y,name, tribe) VALUES (:user,:world,:x,:y,:name,:tribe)")
                            ->execute([
                                ':user' => $_SESSION['id'],
                                ':world' => World::toWorld($post['world']),
                                ':y' => $village['y'],
                                ':x' => $village['x'],
                                ':name' => $village['name'],
                                ':tribe' => $tribe,
                            ]);
                    }
                }
                if ($doc->getElementById('troops') !== null) {
                    $els = $doc->getElementById('troops')->getElementsByTagName('tr');
                    for ($i=1; $i < $els->length -2; $i++) {
                        $tds = $els->item($i)->childNodes;
                        $list = [];
                        for ($j=0;$j< $tds->length; $j++) {
                            if ($tds->item($j)->nodeName === 'td') {
                                $list[] = $tds->item($j)->textContent;
                            }
                        }
                        $this->database
                            ->prepare("UPDATE troops SET soldier1=:soldier1,soldier2=:soldier2,soldier3=:soldier3,soldier4=:soldier4,soldier5=:soldier5,soldier6=:soldier6,settler=:settler,chief=:chief,hero=:hero,ram=:ram,catapult=:catapult,name=:name WHERE user=:user AND world=:world AND x=:x AND y=:y")
                            ->execute([
                                ':user' => $_SESSION['id'],
                                ':world' => World::toWorld($post['world']),
                                ':y' => $villages[$i-1]['y'],
                                ':x' => $villages[$i-1]['x'],
                                ':name' => $villages[$i-1]['name'],
                                ':soldier1' => $list[1],
                                ':soldier2' => $list[2],
                                ':soldier3' => $list[3],
                                ':soldier4' => $list[4],
                                ':soldier5' => $list[5],
                                ':soldier6' => $list[6],
                                ':ram' => $list[7],
                                ':catapult' => $list[8],
                                ':settler' => $list[9],
                                ':chief' => $list[10],
                                ':hero' => $list[11],
                            ]);
                    }
                }
                $this->updateStatistics();
            }
        }
        $stmt = $this->database->prepare("SELECT * FROM troops WHERE user=:id ORDER BY world DESC, tribe DESC, name ASC");
        $stmt->execute([':id' => $_SESSION['id']]);
        $troopsData = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            World::register($this->database, $row['world']);
            $troopsData[$row['world']] = $troopsData[$row['world']] ?? [];
            $troopsData[$row['world']][$row['tribe']] = $troopsData[$row['world']][$row['tribe']] ?? [];
            $troopsData[$row['world']][$row['tribe']][] = $row;
        }
        $stmt = $this->database->prepare("SELECT * FROM troop_updates WHERE user=:id ORDER BY world DESC, `date` ASC");
        $stmt->execute([':id' => $_SESSION['id']]);
        $charts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $charts[$row['world']] = $charts[$row['world']] ?? [
                'offence' => [],
                'multi' => [],
                'defence' => [],
                'scouts' => [],
                'labels' => [],
            ];
            $charts[$row['world']]['offence'][] = intval($row['offensive'], 10);
            $charts[$row['world']]['defence'][] = intval($row['defensive'], 10);
            $charts[$row['world']]['multi'][] = intval($row['multipurpose'], 10);
            $charts[$row['world']]['scouts'][] = intval($row['scouts'], 10);
            $charts[$row['world']]['labels'][] = $row['date'];
        }
        $this->twig->display('troop-tool.twig', [
            'troops' => $troopsData,
            'charts' => $charts,
        ]);
    }
    private function getVillages(DOMDocument $doc): array
    {
        $villages = [];
        $divs = $doc->getElementById('sidebarBoxVillagelist')->getElementsByTagName('div');
        for ($i=0; $i < $divs->length; $i++) {
            if (in_array('villageList', explode(' ', $divs->item($i)->attributes->getNamedItem('class')->textContent), true)) {
                $as = $divs->item($i)->getElementsByTagName('a');
                for ($j=0; $j < $as->length; $j++) {
                    $span = $as->item($j)->nextSibling->nextSibling;
                    $villages[] = [
                        'name' => $span->attributes->getNamedItem('data-villagename')->textContent,
                        'x' => intval($span->attributes->getNamedItem('data-x')->textContent, 10),
                        'y' => intval($span->attributes->getNamedItem('data-y')->textContent, 10),
                    ];
                }
                return $villages;
            }
        }
        throw new UnexpectedValueException('No Village found');
    }
    private function getTribe(DOMDocument $doc): string
    {
        $tribeCheck = array_values(array_filter(explode(' ', $doc->getElementById('questmasterButton')->getAttribute('class')), function ($part) {
            return preg_match('/^vid_[0-9]/', $part);
        }))[0];
        Assert::inArray($tribeCheck, array_keys(self::$tribes), 'Unknown tribe.');
        return self::$tribes[$tribeCheck];
    }
}
