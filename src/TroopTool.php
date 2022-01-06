<?php

namespace De\Idrinth\Travian;

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
    public function run(array $post): void
    {
        if (($_SESSION['id'] ?? 0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        if (isset($post['aid']) && isset($post['type']) && $post['type']==='delete') {
            $this->database
                ->prepare("DELETE FROM troops WHERE user=:id AND aid=:aid")
                ->execute([':id' => $_SESSION['id'], ':aid' => $post['aid']]);
        } elseif (isset($post['world']) && isset($post['tribe']) && isset($post['name']) && isset($post['x']) && isset($post['y'])) {
            if (strpos($post['world'], 'https://') === 0) {
                $post['world'] = substr($post['world'], 8);
            }
            $post['world'] = explode('/', $post['world'])[0];
            Assert::regex($post['world'], '/^ts[0-9]+\.x[0-9]+\.[a-z]+\.travian\.com$/');
            $stmt = $this->database->prepare("SELECT 1 FROM troops WHERE user=:user AND world=:world AND x=:x AND y=:y");
            $stmt->execute([
                ':user' => $_SESSION['id'],
                ':world' => $post['world'],
                ':y' => $post['y'],
                ':x' => $post['x'],
            ]);
            if ($stmt->fetchColumn() === false) {
                $this->database
                    ->prepare("INSERT INTO troops(user,world,x,y,name, tribe) VALUES (:user,:world,:x,:y,:name,:tribe)")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':world' => $post['world'],
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
                        . "SET soldier1=:soldier1, soldier2=:soldier2, soldier3=:soldier3, soldier4=:soldier4, soldier5=:soldier5, soldier6=:soldier6, ram=:ram, catapult=:catapult, settler=:settler, chief=:chief, hero=:hero "
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
                    ]);
            }
        } elseif (isset($post['source']) && $post['source']) {
            $doc = new DOMDocument();
            $doc->loadHTML($post['source']);
            $villages = $this->getVillages($doc);
            $tribe = $this->getTribe($doc);
            foreach ($villages as $village) {
                if (strpos($post['world'], 'https://') === 0) {
                    $post['world'] = substr($post['world'], 8);
                }
                $post['world'] = explode('/', $post['world'])[0];
                Assert::regex($post['world'], '/^ts[0-9]+\.x[0-9]+\.[a-z]+\.travian\.com$/');
                $stmt = $this->database->prepare("SELECT 1 FROM troops WHERE user=:user AND world=:world AND x=:x AND y=:y");
                $stmt->execute([
                    ':user' => $_SESSION['id'],
                    ':world' => $post['world'],
                    ':y' => $village['y'],
                    ':x' => $village['x'],
                ]);
                if ($stmt->fetchColumn() === false) {
                    $this->database
                        ->prepare("INSERT INTO troops(user,world,x,y,name, tribe) VALUES (:user,:world,:x,:y,:name,:tribe)")
                        ->execute([
                            ':user' => $_SESSION['id'],
                            ':world' => $post['world'],
                            ':y' => $village['y'],
                            ':x' => $village['x'],
                            ':name' => $village['name'],
                            ':tribe' => $tribe,
                        ]);
                }
            }
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
                    ->prepare("UPDATE troops SET soldier1=:soldier1,soldier2=:soldier2,soldier3=:soldier3,soldier5=:soldier4,soldier5=:soldier5,soldier6=:soldier6,settler=:settler,chief=:chief,hero=:hero,ram=:ram,catapult=:catapult,name=:name WHERE user=:user AND world=:world AND x=:x AND y=:y")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':world' => $post['world'],
                        ':y' => $villages[$i]['y'],
                        ':x' => $villages[$i]['x'],
                        ':name' => $villages[$i]['name'],
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
        $stmt = $this->database->prepare("SELECT * FROM troops WHERE user=:id ORDER BY world DESC, tribe DESC, name ASC");
        $stmt->execute([':id' => $_SESSION['id']]);
        $troopsData = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $troopsData[$row['world']] = $troopsData[$row['world']] ?? [];
            $troopsData[$row['world']][$row['tribe']] = $troopsData[$row['world']][$row['tribe']] ?? [];
            $troopsData[$row['world']][$row['tribe']][] = $row;
        }
        $this->twig->display('troop-tool.twig', [
            'troops' => $troopsData,
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
