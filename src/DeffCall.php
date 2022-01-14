<?php

namespace De\Idrinth\Travian;

use PDO;
use Ramsey\Uuid\Uuid;

class DeffCall
{
    private $database;
    private $twig;
    private $time;
    private $distance;
    public function __construct(PDO $database, Twig $twig, TravelTime $time, DistanceCalculator $distance)
    {
        $this->database = $database;
        $this->twig = $twig;
        $this->time = $time;
        $this->distance = $distance;
    }
    public function run(array $post, $id, $key=''): void
    {
        $data = [
            'id' => $id,
            'key' => $key,
            'now' => date('Y-m-d H:i:s'),
            'added' => false,
        ];
        if (!Uuid::isValid($id)) {
            header('Location: /deff-call', true, 303);
            return;
        }
        $stmt = $this->database->prepare("SELECT * FROM deff_calls WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $data['target'] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $data['target']) {
            header('Location: /deff-call', true, 303);
            return;
        }
        if ($key && $key !== $data['target']['key']) {
            header('Location: /deff-call/' . $id, true, 303);
            return;
        }
        if ($data['target']['alliance'] == '0') {
            $data['target']['alliance'] = '';
        } else {
            $stmt = $this->database->prepare('SELECT alliances.name, alliances.id, user_alliance.name as player FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user AND user_alliance.alliance=:alliance');
            $stmt->execute([':user' => $_SESSION['id'] ?? 0, ':alliance' => $data['target']['alliance']]);
            $data['target']['alliance'] = $stmt->fetch(PDO::FETCH_ASSOC);
            if (false === $data['target']['alliance']) {
                if ($_SESSION['id'] ?? 0 === 0) {
                    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                    header('Location: /login', true, 303);
                    return;
                }
                header('Location: /deff-call', true, 303);
                return;
            }
        }
        if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
            $this->database
                ->prepare("INSERT IGNORE INTO user_deff_call (user, deff_call) VALUES(:user, :deff_call)")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':deff_call' => $data['target']['aid']
                ]);
            if ($data['target']['key'] === $key) {
                $this->database
                    ->prepare("UPDATE user_deff_call SET advanced=1 WHERE user=:user AND deff_call=:deff_call")
                    ->execute([
                        ':user' => $_SESSION['id'],
                        ':deff_call' => $data['target']['aid']
                    ]);
            }
        }
        if (isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts']+$post['hero'] > 0) && isset($post['time']) && isset($post['date']) && isset($post['account']) && time() < strtotime($data['target']['arrival'])) {
            $stmt = $this->database->prepare("INSERT INTO deff_call_supports (hero, creator, scouts, troops, arrival, deff_call, account) VALUES(:hero, :creator, :scouts, :troops, :arrival, :deff_call, :account)");
            $stmt->execute([
                ':scouts' => intval($post['scouts'], 10),
                ':troops' => intval($post['troops'], 10),
                ':hero' => intval($post['hero'], 10),
                ':account' => $post['account'],
                ':creator' => $_SESSION['id'] ?? 0,
                ':arrival' => $post['date'] . ' ' . $post['time'],
                ':deff_call' => $data['target']['aid'],
            ]);
            header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
            return;
        } elseif (isset($post['amount']) && $post['amount'] >= 0 && isset($post['time']) && isset($post['date']) && isset($post['account']) && time() < strtotime($data['target']['arrival'])) {
            $troops=0;
            $scouts=0;
            $heroes=0;
            if ($post['troop_type'] === 'hero') {
                $heroes++;
            } elseif (in_array($post['troop_type'], ['roman_soldier4', 'gaul_soldier3', 'teuton_soldier4', 'hun_soldier3', 'egyptian_soldier4'], true)) {
                $scouts+= $post['amount'];
            } else {
                $troops = $post['amount'] * Troops::CORN[$post['troop_type']];
            }            
            $stmt = $this->database->prepare("INSERT INTO deff_call_supports (hero, scouts, troops,creator, amount, troop_type, arrival, deff_call, account) VALUES(:hero, :scouts, :troops,:creator, :amount, :troop_type, :arrival, :deff_call, :account)");
            $stmt->execute([
                ':account' => $post['account'],
                ':creator' => $_SESSION['id'] ?? 0,
                ':arrival' => $post['date'] . ' ' . $post['time'],
                ':deff_call' => $data['target']['aid'],
                ':troop_type' => $post['troop_type'],
                ':amount' => $post['amount'],
                ':hero' => $heroes,
                ':troops' => $troops,
                ':scouts' => $scouts,
            ]);
            header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
            return;
        } elseif(isset($post['delete'])) {
            $this->database
                ->prepare('DELETE FROM deff_call_supports WHERE aid=:aid AND creator=:creator AND created>:min')
                ->execute([':aid' => $post['delete'], ':creator' => $_SESSION['id'], ':min' => date('Y-m-d H:i:s', time() - 600)]);
            header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
            return;
        } elseif(isset($post['grain'])) {
            $this->database
                ->prepare('INSERT INTO deff_call_supplies (account, user, deff_call, grain, arrival) VALUES (:account, :creator, :deff_call, :grain, :arrival)')
                ->execute([':account' => $post['account'], ':creator' => $_SESSION['id'], ':deff_call' => $data['target']['aid'], ':grain' => $post['grain'], ':arrival' => $post['date'] . ' ' . $post['time']]);
            header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
            return;
        }
        $stmt = $this->database->prepare("SELECT deff_call_supports.*,users.name,users.discriminator FROM deff_call_supports LEFT JOIN users ON deff_call_supports.creator=users.aid WHERE deff_call=:id ORDER BY deff_call_supports.arrival ASC");
        $stmt->execute([':id' => $data['target']['aid']]);
        $data['supports'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data['troops'] = [
            'hero' => 0,
            'roman_soldier1' => 0,
            'roman_soldier2' => 0,
            'roman_soldier3' => 0,
            'roman_soldier4' => 0,
            'roman_soldier5' => 0,
            'roman_soldier6' => 0,
            'gaul_soldier1' => 0,
            'gaul_soldier2' => 0,
            'gaul_soldier3' => 0,
            'gaul_soldier4' => 0,
            'gaul_soldier5' => 0,
            'gaul_soldier6' => 0,
            'teuton_soldier1' => 0,
            'teuton_soldier2' => 0,
            'teuton_soldier3' => 0,
            'teuton_soldier4' => 0,
            'teuton_soldier5' => 0,
            'teuton_soldier6' => 0,
            'hun_soldier1' => 0,
            'hun_soldier2' => 0,
            'hun_soldier3' => 0,
            'hun_soldier4' => 0,
            'hun_soldier5' => 0,
            'hun_soldier6' => 0,
            'egyptian_soldier1' => 0,
            'egyptian_soldier2' => 0,
            'egyptian_soldier3' => 0,
            'egyptian_soldier4' => 0,
            'egyptian_soldier5' => 0,
            'egyptian_soldier6' => 0,
        ];
        foreach ($data['supports'] as $support) {
            if (intval($support['amount'], 10) > 0 && $support['arrival'] <= $data['target']['arrival']) {
                $data['troops'][$support['troop_type']] += intval($support['amount'], 10);
            }
        }
        if (($_SESSION['id']??0) > 0) {
            $remaining = strtotime($data['target']['arrival']) - time();
            $stmt = $this->database->prepare("SELECT * FROM troops WHERE user=:id AND world=:world");
            $stmt->execute([':id' => $_SESSION['id'], ':world' => $data['target']['world']]);
            $data['own'] = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $village) {
                $distance = $this->distance->distance(
                    new Point($village['x'], $village['y']),
                    new Point($data['target']['x'], $data['target']['y']),
                    intval($data['target']['world_width'], 10),
                    intval($data['target']['world_height'], 10),
                    true
                );
                $boots = 0;
                $standard=0;
                if ($village['hero'] == 1) {
                    $stmt2 = $this->database->prepare('SELECT boot_bonus,standard_bonus FROM my_hero WHERE user=:user AND world=:world');
                    $stmt2->execute([':user' => $_SESSION['id'], ':world' => $village['world']]);
                    list($boots, $standard) = $stmt2->fetch(PDO::FETCH_NUM);
                    $boots = intval($boots, 10);
                    $standard = intval($standard, 10);
                    $data['own'][$village['name']]['hero'] = [
                        'troops' => 1,
                        'boots' => $boots,
                        'standard' => $standard,
                    ];
                }
                $found = false;
                for ($i=1;$i<7;$i++) {
                    $time = $this->time->time($distance, Troops::SPEED[$village['tribe'] . '_soldier' . $i], $standard/100, $village['tournament_square'], $boots/100, 0)[0];
                    if (intval($village['soldier' . $i], 10) > 0 && $time < $remaining) {
                        $data['own'][$village['name']] = $data['own'][$village['name']] ?? [];
                        $data['own'][$village['name']][$village['tribe'] . '_soldier' . $i] = [
                            'troops' => intval($village['soldier' . $i], 10),
                            'duration' => $time,
                        ];
                        $found = true;
                    }
                }
                if (!$found) {
                    unset($data['own'][$village['name']]);
                }
            }
        }
        $data['charts'] = [
            'labels' => [$data['target']['created']],
            'data' => [$data['target']['grain']],
            'max' => [$data['target']['grain_storage']]
        ];
        $stmt3 = $this->database->prepare('SELECT * FROM deff_call_supplies WHERE deff_call=:dc');
        $stmt3->execute([':dc' => $data['target']['aid']]);
        $supplies = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        for($i = strtotime($data['target']['created']) + 600; $i <= strtotime($data['target']['arrival']) + 7200; $i += 600) {
            $troops = 0;
            foreach ($data['supports'] as $support) {
                if (strtotime($support['arrival']) < $i) {
                    $troops += $support['troops'] + 6*$support['hero']+2*$support['scouts'];
                }
            }
            $corn = 0;
            foreach ($supplies as $supply) {
                if (strtotime($supply['arrival']) < $i && strtotime($supply['arrival']) >= $i - 600) {
                    $corn += $supply['grain'];
                }
            }
            $data['charts']['labels'][] = date('Y-m-d H:i:s', $i);
            $data['charts']['data'][] = max(0, min($data['target']['grain_storage'], floor($data['charts']['data'][count($data['charts']['data']) - 1] - $troops/6 + $data['target']['grain_production']/6 + $corn)));
            $data['charts']['max'][] = $data['target']['grain_storage'];
        }
        $data['corn'] = Troops::CORN;
        WorldImporter::register($this->database, $data['target']['world']);
        $this->twig->display($data['target']['advanced_troop_data'] ? 'advanced-deff-call.twig' : 'deff-call.twig', $data);
    }
}
