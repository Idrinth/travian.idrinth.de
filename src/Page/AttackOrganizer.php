<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Point;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Twig;
use PDO;
use Ramsey\Uuid\Uuid;

class AttackOrganizer
{
    private $twig;
    private $database;
    private $time;
    private $distance;
    public function __construct(PDO $database, Twig $twig, TravelTime $time, DistanceCalculator $distance)
    {
        $this->database = $database;
        $this->twig = $twig;
        $this->distance = $distance;
        $this->time = $time;
    }
    public function run($post, $allianceId, $id = '')
    {
        if (($_SESSION['id']??0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        $stmt = $this->database->prepare("SELECT alliances.*,user_alliance.rank FROM alliances INNER JOIN user_alliance ON user_alliance.alliance=alliances.aid AND user_alliance.rank IN('Planner', 'High Council', 'Creator') WHERE alliances.id=:id");
        $stmt->execute([':id' => $allianceId]);
        $alliance = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $alliance) {
            header('Location: /alliance/' . $allianceId, true, 303);
            return;
        }
        if ($id) {
            $stmt = $this->database->prepare('SELECT * FROM attack_plan WHERE id=:id AND alliance=:alliance');
            $stmt->execute([':alliance' => $alliance['aid'], ':id' => $id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($plan === false) {
                header('Location: /alliance/' . $allianceId, true, 303);
                return;
            }
            if (isset($post['attack'])) {
                $this->database
                    ->prepare('UPDATE attack_plan_unit SET sent=:now WHERE user=:user AND aid=:aid')
                    ->execute([':aid' => $post['attack'], ':user' => $_SESSION['id'], ':now' => date('Y-m-d H:i:s')]);
            }
            if (in_array($alliance['rank'], ['Planner', 'High Council', 'Creator'])) {
                $stmt22 = $this->database->prepare('SELECT attack_plan_unit.*,troops.tournament_square,CONCAT(users.name, \'#\', users.discriminator) AS discord,user_world.name AS ingame
FROM attack_plan_unit
INNER JOIN users ON users.aid=attack_plan_unit.user
LEFT JOIN user_world ON user_world.world=:world AND user_world.user=users.aid
LEFT JOIN troops ON troops.x=attack_plan_unit.origin_x AND troops.y=attack_plan_unit.origin_y AND troops.user=attack_plan_unit.user AND troops.world=:world
WHERE attack_plan=:attack_plan');
                $stmt22->execute([':attack_plan' => $plan['aid'], ':world' => $alliance['world']]);
            } else {
                $stmt22 = $this->database->prepare('SELECT attack_plan_unit.*,troops.tournament_square,CONCAT(users.name, \'#\', users.discriminator) AS discord,user_world.name AS ingame
FROM attack_plan_unit
INNER JOIN users ON users.aid=attack_plan_unit.user
LEFT JOIN user_world ON user_world.world=:world AND user_world.user=users.aid
LEFT JOIN troops ON troops.x=attack_plan_unit.origin_x AND troops.y=attack_plan_unit.origin_y AND troops.user=attack_plan_unit.user AND troops.world=:world
WHERE attack_plan=:attack_plan
AND attack_plan_unit.user=:user');
                $stmt22->execute([':attack_plan' => $plan['aid'], ':user' => $_SESSION['id'], ':world' => $alliance['world']]);
            }
            $this->twig->display('attack-organizer.twig', [
                'world' => $alliance['world'],
                'plan' => $plan,
                'attacks' => array_map(function($row) use($plan) {
                    $distance = $this->distance->distance(new Point(intval($row['origin_x'], 10), intval($row['origin_y'], 10)), new Point(intval($row['target_x'], 10), intval($row['target_y'], 10)), 401, 401, true);
                    $row['start'] = date(
                        'Y-m-d H:i:s',
                        strtotime($plan['arrival']) - $this->time->time($distance, 3, 0, $row['tournament_square'], 0, 0)[0]
                    );
                    $row['distance'] = $distance;
                    return $row;
                }, $stmt22->fetchAll(PDO::FETCH_ASSOC)),
            ]);
            return;
        } elseif (!in_array($alliance['rank'], ['Planner', 'High Council', 'Creator'])) {
            header('Location: /alliance/' . $allianceId, true, 303);
            return;
        }
        if (isset($post['off_catapults'])) {
            $alliances = array_map(function($string) {
                return $this->database->quote($string);
            },array_map('trim', array_map('strtolower', explode(',', $post['alliances']))));
            $targets = $this->database->prepare("SELECT player_name AS player,village_id AS id,village_name AS name,x,y,is_capital AS isCapital FROM `{$alliance['world']}` WHERE population >= :population AND LOWER(alliance_name) IN (".implode(',', $alliances).") ");
            $targets->execute([':population' => $post['population']]);
            $offs = $this->database->prepare('SELECT catapult,user_world.name AS player,troops.aid,troops.`name`,troops.`x`,troops.`y`,tournament_square
FROM user_alliance
INNER JOIN alliances ON alliances.aid=user_alliance.alliance                          
INNER JOIN troops ON troops.user=user_alliance.user AND troops.world=alliances.world  
LEFT JOIN user_world ON user_world.user=user_alliance.user AND user_world.world=alliances.world
WHERE user_alliance.alliance=:alliance
AND troops.catapult >= :catapults');
            $offs->execute([':alliance' => $alliance['aid'], ':catapults' => $post['off_catapults']]);
            $fakes = $this->database->prepare('SELECT catapult,user_world.name AS player,troops.aid,troops.`name`,troops.`x`,troops.`y`,tournament_square
FROM user_alliance
INNER JOIN alliances ON alliances.aid=user_alliance.alliance                          
INNER JOIN troops ON troops.user=user_alliance.user AND troops.world=alliances.world
LEFT JOIN user_world ON user_world.user=user_alliance.user AND user_world.world=alliances.world
WHERE user_alliance.alliance=:alliance
AND troops.catapult >= :catapults');
            $fakes->execute([':alliance' => $alliance['aid'], ':catapults' => $post['fake_catapults']]);
            $this->twig->display('attack-organizer-step2.twig', [
                'targets' => array_map(function ($row) {
                    return [
                        'id' => intval($row['id'], 10),
                        'player' => $row['player'],
                        'name' => $row['name'],
                        'x' => intval($row['x'], 10),
                        'y' => intval($row['y'], 10),
                        'isCapital' => intval($row['isCapital'], 10),
                    ];
                }, $targets->fetchAll(PDO::FETCH_ASSOC)),
                'inputs' => $post,
                'offs' => array_map(function ($row) {
                    return [
                        'catapult' => intval($row['catapult'], 10),
                        'player' => $row['player'],
                        'aid' => $row['aid'],
                        'name' => $row['name'],
                        'x' => intval($row['x'], 10),
                        'y' => intval($row['y'], 10),
                        'boot_bonus' => 0,
                        'tournament_square' => intval($row['tournament_square'], 10),
                    ];
                }, $offs->fetchAll(PDO::FETCH_ASSOC)),
                'fakes' => array_map(function ($row) {
                    return [
                        'catapult' => intval($row['catapult'], 10),
                        'player' => $row['player'],
                        'aid' => $row['aid'],
                        'name' => $row['name'],
                        'x' => intval($row['x'], 10),
                        'y' => intval($row['y'], 10),
                        'boot_bonus' => 0,
                        'tournament_square' => intval($row['tournament_square'], 10),
                    ];
                }, $fakes->fetchAll(PDO::FETCH_ASSOC)),
            ]);
            return;
        } elseif(isset($post['offs']) && isset($post['fakes']) && isset($post['date']) && isset($post['time'])) { 
            $attack = Uuid::uuid4();
            $stmt = $this->database->prepare('INSERT INTO attack_plan (id,alliance,arrival) VALUES (:id,:alliance,:arrival)');
            $stmt->execute([':id' => $attack, ':alliance' => $alliance['aid'], ':arrival' => $post['date'] . ' ' . $post['time']]);
            $id = $this->database->lastInsertId();
            foreach ($post['offs'] as $village => $offs) {
                $stmt = $this->database->prepare("SELECT x,y FROM `{$alliance['world']}` WHERE village_id=:id");
                $stmt->execute([':id' => $village]);
                $vData = $stmt->fetch(PDO::FETCH_ASSOC);
                foreach ($offs as $off => $waves) {
                    if ($waves==0) {
                        continue;
                    }
                    $stmt = $this->database->prepare('SELECT x,y,troops.user
FROM troops
INNER JOIN user_alliance ON user_alliance.user=troops.user AND user_alliance.alliance=:alliance 
INNER JOIN alliances ON user_alliance.alliance=alliances.aid
WHERE troops.aid=:aid');
                    $stmt->execute([':aid' => $off, ':alliance' => $alliance['aid']]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->database
                            ->prepare('INSERT INTO attack_plan_unit (attack_plan,user,type,waves,target_x,target_y,origin_x,origin_y) VALUES (:attack_plan,:user,:type,:waves,:target_x,:target_y,:origin_x,:origin_y)')
                            ->execute([
                                ':attack_plan' => $id,
                                ':user' => $data['user'],
                                ':type' => 'Real',
                                ':waves' => $waves,
                                ':target_x' => $vData['x'],
                                ':target_y' => $vData['y'],
                                ':origin_x' => $data['x'],
                                ':origin_y' => $data['y'],
                            ]);
                }
            }
            foreach ($post['fakes'] as $village => $offs) {
                $stmt = $this->database->prepare("SELECT x,y FROM `{$alliance['world']}` WHERE village_id=:id");
                $stmt->execute([':id' => $village]);
                $vData = $stmt->fetch(PDO::FETCH_ASSOC);
                foreach ($offs as $off => $waves) {
                    if ($waves==0) {
                        continue;
                    }
                    $stmt = $this->database->prepare('SELECT x,y,troops.user
FROM troops
INNER JOIN user_alliance ON user_alliance.user=troops.user AND user_alliance.alliance=:alliance 
INNER JOIN alliances ON user_alliance.alliance=alliances.aid
WHERE troops.aid=:aid');
                    $stmt->execute([':aid' => $off, ':alliance' => $alliance['aid']]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->database
                            ->prepare('INSERT INTO attack_plan_unit (attack_plan,user,type,waves,target_x,target_y,origin_x,origin_y) VALUES (:attack_plan,:user,:type,:waves,:target_x,:target_y,:origin_x,:origin_y)')
                            ->execute([
                                ':attack_plan' => $id,
                                ':user' => $data['user'],
                                ':type' => 'Fake',
                                ':waves' => $waves,
                                ':target_x' => $vData['x'],
                                ':target_y' => $vData['y'],
                                ':origin_x' => $data['x'],
                                ':origin_y' => $data['y'],
                            ]);
                }
            }
            header('Location: /alliance/' . $allianceId . '/attack-organizer/' . $attack, true, 303);
            return;
        } elseif(isset($post['offs']) && isset($post['fakes'])) {
            $villages = [];
            $maxWalk = 0;
            foreach ($post['offs'] as $village => $offs) {
                $stmt = $this->database->prepare("SELECT village_id AS id,x,y,village_name as name, player_name AS player FROM `{$alliance['world']}` WHERE village_id=:id");
                $stmt->execute([':id' => $village]);
                $vData = $stmt->fetch(PDO::FETCH_ASSOC);
                $villages[$village] = $vData;
                foreach ($offs as $off) {
                    $stmt = $this->database->prepare('SELECT tournament_square,x,y,troops.name,catapult,IF(troops.hero,boot_bonus,0)
FROM troops
INNER JOIN user_alliance ON user_alliance.user=troops.user AND user_alliance.alliance=:alliance 
INNER JOIN alliances ON user_alliance.alliance=alliances.aid 
LEFT JOIN my_hero ON my_hero.user=troops.user AND alliances.world=my_hero.world
WHERE troops.aid=:aid');
                    $stmt->execute([':aid' => $off, ':alliance' => $alliance['aid']]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $distance = $this->distance->distance(new Point(intval($data['x'], 10), intval($data['y'], 10)), new Point(intval($vData['x'], 10), intval($vData['y'], 10)), 401, 401, true);
                    $f = [
                        'distance' => $distance,
                        'time' => $this->time->time($distance, 3, 0, intval($data['tournament_square'], 10), intval($data['boot_bonus'], 10), 0)[0],
                        'name' => $data['name'],
                        'aid' => $off,
                        'catapults' => $data['catapult']
                    ];
                    $maxWalk = max($f['time'], $maxWalk);
                    $f['time'] = floor($f['time']/3600) . ':' . floor(($f['time']%3600)/60) . ':' . ($f['time']%60);
                    $villages[$village]['offs'][] = $f;
                }
            }
            foreach ($post['fakes'] as $village => $fakes) {
                $stmt = $this->database->prepare("SELECT x,y,village_name as name, player_name AS player FROM `{$alliance['world']}` WHERE village_id=:id");
                $stmt->execute([':id' => $village]);
                $vData = $stmt->fetch(PDO::FETCH_ASSOC);
                $villages[$village] = $villages[$village] ??$vData;
                foreach ($fakes as $fake) {
                    if (in_array($fake, $post['offs'][$village], true)) {
                        continue;
                    }
                    $stmt = $this->database->prepare('SELECT tournament_square,x,y,name,catapult FROM troops WHERE aid=:aid AND user IN (SELECT user FROM user_alliance WHERE alliance=:alliance)');
                    $stmt->execute([':aid' => $fake, ':alliance' => $alliance['aid']]);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $distance = $this->distance->distance(new Point(intval($data['x'], 10), intval($data['y'], 10)), new Point(intval($vData['x'], 10), intval($vData['y'], 10)), 401, 401, true);
                    $shoes = 0;
                    $f = [
                        'distance' => $distance,
                        'time' => $this->time->time($distance, 3, 0, intval($data['tournament_square'], 10), $shoes, 0)[0],
                        'name' => $data['name'],
                        'aid' => $fake,
                        'catapults' => $data['catapult']
                    ];
                    $maxWalk = max($f['time'], $maxWalk);
                    $f['time'] = floor($f['time']/3600) . ':' . floor(($f['time']%3600)/60) . ':' . ($f['time']%60);
                    $villages[$village]['fakes'][] = $f;
                }
            }
            $maxWalk = floor($maxWalk/3600) . ':' . floor(($maxWalk%3600)/60) . ':' . ($maxWalk%60);
            $this->twig->display('attack-organizer-step3.twig', ['villages' => $villages, 'maxWalk' => $maxWalk]);
            return;
        }
        $this->twig->display('attack-organizer-step1.twig');
    }
}
