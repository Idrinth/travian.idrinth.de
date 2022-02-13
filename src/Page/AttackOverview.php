<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Point;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Twig;
use PDO;

class AttackOverview
{
    private $database;
    private $twig;
    private $distance;
    private $time;
    public function __construct(PDO $database, Twig $twig, DistanceCalculator $distance, TravelTime $time)
    {
        $this->database = $database;
        $this->twig = $twig;
        $this->distance = $distance;
        $this->time = $time;
    }
    public function run($post, $allianceId='')
    {
        if (($_SESSION['id']??0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        if ($allianceId === '') {
            if (isset($post['alliance']) && $post['alliance']) {
                header('Location: /alliance/'.$post['alliance'].'/attack-overview', true, 303);
                return;
            }
            $stmt = $this->database->prepare("SELECT alliances.*,user_alliance.rank FROM alliances INNER JOIN user_alliance ON user_alliance.alliance=alliances.aid WHERE user_alliance.user=:id");
            $stmt->execute([':id' => $_SESSION['id']]);
            $this->twig->display('attack-overview0.twig', [
                'alliances' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
            return;
        }
        $stmt = $this->database->prepare('SELECT alliances.*,user_alliance.rank FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance WHERE user_alliance.user=:user AND alliances.id=:id');
        $stmt->execute([':id' => $allianceId, ':user' => $_SESSION['id']]);
        $alliance = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $alliance) {
            header('Location: /attack-overview', true, 303);
            return;
        }
        if (isset($post['fromX'])) {
            $this->database->prepare('INSERT INTO alliance_attacks (alliance,fromX,fromY,toX,toY,arrival,earliestStart,latestStart,`user`) VALUES (:alliance,:fromX,:fromY,:toX,:toY,:arrival,:earliestStart,:latestStart,:user)')
                ->execute([
                    'alliance' => $alliance['aid'],
                    'fromX' => $post['fromX'],
                    'fromY' => $post['fromY'],
                    'toX' => $post['toX'],
                    'toY' => $post['toY'],
                    'arrival' => $post['date'].' '.$post['time'],
                    'earliestStart' => date('Y-m-d H:i:s', time() - explode(':', $post['blind_time'])[0]*3600 - explode(':', $post['blind_time'])[1]*60 - explode(':', $post['blind_time'])[2]),
                    'latestStart' => date('Y-m-d H:i:s'),
                    'user' => $_SESSION['id'],
                ]);
        }
        $stmt = $this->database->prepare('SELECT * FROM alliance_attacks WHERE alliance=:id AND arrival>=:now');
        $stmt->execute([':now' => date('Y-m-d H:i:s'), ':id' => $alliance['aid']]);
        $cache = [];
        $stmt2 = $this->database->prepare('SELECT world_players.name
FROM world_players
INNER JOIN world_villages ON world_players.world=world_villages.world AND world_villages.player=world_players.id
WHERE world_villages.world=:world AND world_villages.x=:x AND world_villages.y=:y
ORDER BY world_villages.day DESC
LIMIT 1');
        $this->twig->display('attack-overview1.twig', [
            'attacks' => array_map(function (array $row) use($alliance, &$cache, &$stmt2) {
                $speeds = $this->time->find(
                    $this->distance->distance(
                        new Point($row['fromX'], $row['fromY']),
                        new Point($row['toX'], $row['toY']),
                        401,
                        401,
                        true
                    ),
                    0,
                    0,
                    strtotime($row['arrival']) - strtotime($row['latestStart']),
                    strtotime($row['latestStart']) - strtotime($row['earliestStart'])
                );
                foreach ($speeds as $speed) {
                    $row['speed'][$speed[0]] = $row['speed'][$speed[0]] ?? [$speed[0], $speed[1]];
                }
                if (isset($cache[$row['fromX'].'#'.$row['fromY']])) {
                    $row['fromName'] = $cache[$row['fromX'].'#'.$row['fromY']];
                } else {
                    $stmt2->closeCursor();
                    $stmt2->execute([':world' => $alliance['world'], ':x' => $row['fromX'], ':y' => 'fromY']);
                    $row['fromName'] = $stmt2->fetchColumn();
                    $cache[$row['fromX'].'#'.$row['fromY']] = $row['fromName'];
                }
                if (isset($cache[$row['toX'].'#'.$row['toY']])) {
                    $row['toName'] = $cache[$row['toX'].'#'.$row['toY']];
                } else {
                    $stmt2->closeCursor();
                    $stmt2->execute([':world' => $alliance['world'], ':x' => $row['toX'], ':y' => 'toY']);
                    $row['toName'] = $stmt2->fetchColumn();
                    $cache[$row['toX'].'#'.$row['toY']] = $row['toName'];
                }                
                return $row;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC)),
        ]);
    }
}
