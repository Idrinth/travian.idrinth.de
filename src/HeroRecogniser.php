<?php

namespace De\Idrinth\Travian;

use PDO;

class HeroRecogniser
{
    private static $helmet = [
        0 => 'empty',
        1 => 'small_xp_helmet',
        2 => 'medium_xp_helmet',
        3 => 'great_xp_helmet',
        4 => 'small_regen_helmet',
        5 => 'medium_regen_helmet',
        6 => 'great_regen_helmet',
        7 => 'small_cp_helmet',
        8 => 'medium_cp_helmet',
        9 => 'great_cp_helmet',
        10 => 'small_cavalry_helmet',
        11 => 'medium_cavalry_helmet',
        12 => 'great_cavalry_helmet',
        13 => 'small_infantry_helmet',
        14 => 'medium_infantry_helmet',
        15 => 'great_infantry_helmet',
    ];
    private static $armor = [
        0 => 'empty',
        82 => 'small_regen_armor',
        83 => 'medium_regen_armor',
        84 => 'great_regen_armor',
        85 => 'small_scales_armor',
        86 => 'medium_scales_armor',
        87 => 'great_scales_armor',
        88 => 'small_chest_armor',
        89 => 'medium_chest_armor',
        90 => 'great_chest_armor',
        91 => 'small_chain_armor',
        92 => 'medium_chain_armor',
        93 => 'great_chain_armor',
    ];
    private static $right = [
        0 => 'empty',
        16 => 'small_legionaire',
        17 => 'medium_legionaire',
        18 => 'great_legionaire',
        19 => 'small_praetorian',
        20 => 'medium_praetorian',
        21 => 'great_praetorian',
        22 => 'small_imperian',
        23 => 'medium_imperian',
        24 => 'great_imperian',
        25 => 'small_imperatoris',
        26 => 'medium_imperatoris',
        27 => 'great_imperatoris',
        28 => 'small_caesaris',
        29 => 'medium_caesaris',
        30 => 'great_caesaris',
        31 => 'small_phalanx',
        32 => 'medium_phalanx',
        33 => 'great_phalanx',
        34 => 'small_sword',
        35 => 'medium_sword',
        36 => 'great_sword',
        37 => 'small_theutates',
        38 => 'medium_theutates',
        39 => 'great_theutates',
        40 => 'small_druid',
        41 => 'medium_druid',
        42 => 'great_druid',
        43 => 'small_haeduaner',
        44 => 'medium_haeduaner',
        45 => 'great_haeduaner',
        46 => 'small_club',
        47 => 'medium_club',
        48 => 'great_club',
        49 => 'small_speer',
        50 => 'medium_speer',
        51 => 'great_speer',
        52 => 'small_axe',
        53 => 'medium_axe',
        54 => 'great_axe',
        55 => 'small_knight',
        56 => 'medium_knight',
        57 => 'great_knight',
        58 => 'small_teuton',
        59 => 'medium_teuton',
        60 => 'great_tueton',
        115 => 'small_slave',
        116 => 'medium_slave',
        117 => 'great_slave',
        118 => 'small_ash',
        119 => 'medium_ash',
        120 => 'great_ash',
        121 => 'small_warrior',
        122 => 'medium_warrior',
        123 => 'great_warrior',
        124 => 'small_anhor',
        125 => 'medium_anhor',
        126 => 'great_anhor',
        127 => 'small_resheph',
        128 => 'medium_resheph',
        129 => 'great_resheph',
        130 => 'small_mercenary',
        131 => 'medium_mercenary',
        132 => 'great_mercenary',
        133 => 'small_bownmen',
        134 => 'medium_bowmen',
        135 => 'great_bowmen',
        136 => 'small_steppe',
        137 => 'medium_steppe',
        138 => 'great_steppe',
        139 => 'small_marksman',
        140 => 'medium_marksman',
        141 => 'great_marksman',
        142 => 'small_marauder',
        143 => 'medium_marauder',
        144 => 'great_marauder',
    ];
    private static $left = [
        0 => 'empty',
        61 => 'small_map',
        62 => 'medium_map',
        63 => 'great_map',
        64 => 'small_people_banner',
        65 => 'medium_people_banner',
        66 => 'great_people_banner',
        67 => 'small_alliance_banner',
        68 => 'medium_alliance_banner',
        69 => 'great_alliance_banner',
        73 => 'small_thief',
        74 => 'medium_thief',
        75 => 'great_thief',
        76 => 'small_shield',
        77 => 'medium_shield',
        78 => 'great_shield',
        79 => 'small_natars',
        80 => 'medium_natars',
        81 => 'great_natars',
    ];
    private static $shoes = [
        0 => 'empty',
        94 => 'small_regen_shoes',
        95 => 'medium_regen_shoes',
        96 => 'great_regen_shoes',
        97 => 'small_speed_shoes',
        98 => 'medium_speed_shoes',
        99 => 'great_speed_shoes',
        100 => 'small_horse_shoes',
        101 => 'medium_horse_shoes',
        102 => 'great_horse_shoes',
    ];
    private static $horse = [
        0 => 'empty',
        103 => 'gelding',
        104 => 'thoroughbred',
        105 => 'warhorse',
    ];
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post, $id = 0): void
    {
        $data = [
            'inputs' => [
                'url' => $post['url'] ?? '',
                'hero_share' => intval($post['hero_share'] ?? 0, 10),
                'player_id' => intval($post['player_id'] ?? 0, 10),
            ],
            'result' => [],
        ];
        if ($id) {
            $stmt = $this->database->prepare("SELECT * FROM hero WHERE aid=:id");
            $stmt->execute([
                ':id' => $id,
            ]);
            $data['previously'] = $stmt->fetch(PDO::FETCH_ASSOC);
            if (false === $data['previously']) {
                header('Location: /hero-check', 303, true);
                return;
            }
            $stmt = $this->database->prepare("SELECT rank FROM user_alliance WHERE alliance=:alliance AND user=:user");
            $stmt->execute([
                ':user' => $_SESSION['id'] ?? 0,
                ':alliance' => $data['previously']['alliance'],
            ]);
            $rank = $stmt->fetchColumn();
            if (false===$rank) {
                header('Location: /hero-check', 303, true);
                return;
            }
            $data['result']['horse'] = self::$horse[intval($data['previously']['horse'], 10)];
            $data['result']['left_hand'] = self::$left[intval($data['previously']['left_hand'], 10)];
            $data['result']['right_hand'] = self::$right[intval($data['previously']['right_hand'], 10)];
            $data['result']['shoes'] = self::$shoes[intval($data['previously']['shoes'], 10)];
            $data['result']['armor'] = self::$armor[intval($data['previously']['armor'], 10)];
            $data['result']['helmet'] = self::$helmet[intval($data['previously']['helmet'], 10)];
            $data['previously']['horse'] = self::$horse[intval($data['previously']['horse'], 10)];
            $data['previously']['left_hand'] = self::$left[intval($data['previously']['left_hand'], 10)];
            $data['previously']['right_hand'] = self::$right[intval($data['previously']['right_hand'], 10)];
            $data['previously']['shoes'] = self::$shoes[intval($data['previously']['shoes'], 10)];
            $data['previously']['armor'] = self::$armor[intval($data['previously']['armor'], 10)];
            $data['previously']['helmet'] = self::$helmet[intval($data['previously']['helmet'], 10)];
            $data['inputs']['hero_share'] = intval($data['previously']['alliance'], 10);
            $data['inputs']['player_id'] = intval($data['previously']['player'], 10);
        } elseif ($data['inputs']['url']) {
            $ids = substr($data['inputs']['url'], strpos($data['inputs']['url'], '/body/') + 6, 68);
            $data['result']['horse'] = self::$horse[hexdec(substr($ids, 40, 2))];
            $data['result']['left_hand'] = self::$left[hexdec(substr($ids, 56, 2))];
            $data['result']['right_hand'] = self::$right[hexdec(substr($ids, 52, 2))];
            $data['result']['shoes'] = self::$shoes[hexdec(substr($ids, 64, 2))];
            $data['result']['armor'] = self::$armor[hexdec(substr($ids, 60, 2))];
            $data['result']['helmet'] = self::$helmet[hexdec(substr($ids, 49, 1))];
            if (isset($post['player_id']) && $post['player_id'] > 0 && isset($post['hero_share']) && $post['hero_share'] > 0) {
                $data['inputs']['player_id'] = $post['player_id'];
                $data['inputs']['hero_share'] = $post['hero_share'];
                $stmt = $this->database->prepare("SELECT 1 FROM user_alliance WHERE user=:user AND rank<>'Follower' AND alliance=:alliance");
                $stmt->execute([':user' => $_SESSION['id'], ':alliance' => $post['hero_share']]);
                $allowed = (bool) $stmt->fetchColumn();
                if ($allowed) {
                    $now = date('Y-m-d H:i:s');
                    $stmt = $this->database->prepare("SELECT * FROM hero WHERE alliance=:alliance AND player=:player");
                    $stmt->execute(
                        [':player' => $post['player_id'], ':alliance' => $post['hero_share']]
                    );
                    $data['previously'] = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (false === $data['previously']) {
                        $this->database
                            ->prepare("INSERT INTO hero (player, alliance, name) VALUES (:player, :alliance, :name)")
                            ->execute([':player' => $post['player_id'], ':alliance' => $post['hero_share'], ':name' => $post['name']??'']);
                        $stmt = $this->database->prepare("SELECT * FROM hero WHERE alliance=:alliance AND player=:player");
                        $stmt->execute(
                            [':player' => $post['player_id'], ':alliance' => $post['hero_share']]
                        );
                        $data['previously'] = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    $this->database
                        ->prepare("INSERT IGNORE INTO hero_updates (hero,user,date) VALUES (:hero,:user,:date)")
                        ->execute([
                            ':user' => $_SESSION['id'],
                            ':hero' => $data['previously']['aid'],
                            ':date' => date('Y-m-d'),
                        ]);
                    $data['previously']['horse'] = self::$horse[intval($data['previously']['horse'], 10)];
                    $data['previously']['left_hand'] = self::$left[intval($data['previously']['left_hand'], 10)];
                    $data['previously']['right_hand'] = self::$right[intval($data['previously']['right_hand'], 10)];
                    $data['previously']['shoes'] = self::$shoes[intval($data['previously']['shoes'], 10)];
                    $data['previously']['armor'] = self::$armor[intval($data['previously']['armor'], 10)];
                    $data['previously']['helmet'] = self::$helmet[intval($data['previously']['helmet'], 10)];
                    if ($data['previously']['name'] !== $post['name']) {
                        $this->database
                            ->prepare("UPDATE hero SET name=:name WHERE aid=:id")
                            ->execute([':id' => $data['previously']['aid'], ':name' => $post['name']??'']);
                    }
                    if ($data['previously']['horse'] === $data['result']['horse']) {
                        $this->database
                            ->prepare("UPDATE hero SET horse_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET horse_last_seen=:now, horse_first_seen=:now, horse=:horse, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':horse' => array_flip(self::$horse)[$data['result']['horse']]]);
                    }
                    if ($data['previously']['left_hand'] === $data['result']['left_hand']) {
                        $this->database
                            ->prepare("UPDATE hero SET left_hand_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET left_hand_last_seen=:now, left_hand_first_seen=:now, left_hand=:left_hand, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':left_hand' => array_flip(self::$left)[$data['result']['left_hand']]]);
                    }
                    if ($data['previously']['right_hand'] === $data['result']['right_hand']) {
                        $this->database
                            ->prepare("UPDATE hero SET right_hand_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET right_hand_last_seen=:now, right_hand_first_seen=:now, right_hand=:right_hand, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':right_hand' => array_flip(self::$right)[$data['result']['right_hand']]]);
                    }
                    if ($data['previously']['shoes'] === $data['result']['shoes']) {
                        $this->database
                            ->prepare("UPDATE hero SET shoes_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET shoes_last_seen=:now, shoes_first_seen=:now, shoes=:shoes, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':shoes' => array_flip(self::$shoes)[$data['result']['shoes']]]);
                    }
                    if ($data['previously']['armor'] === $data['result']['armor']) {
                        $this->database
                            ->prepare("UPDATE hero SET armor_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET armor_last_seen=:now, armor_first_seen=:now, armor=:armor, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':armor' => array_flip(self::$armor)[$data['result']['armor']]]);
                    }
                    if ($data['previously']['helmet'] === $data['result']['helmet']) {
                        $this->database
                            ->prepare("UPDATE hero SET helmet_last_seen=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid']]);
                    } else {
                        $this->database
                            ->prepare("UPDATE hero SET helmet_last_seen=:now, helmet_first_seen=:now, helmet=:helmet, last_change=:now, last_update=:now WHERE aid=:id")
                            ->execute([':now' => $now, ':id' => $data['previously']['aid'], ':helmet' => array_flip(self::$helmet)[$data['result']['helmet']]]);
                    }
                }
            }
        }
        if ($_SESSION['id'] > 0) {
            $stmt = $this->database->prepare(
                "SELECT alliances.name, alliances.world, alliances.aid "
                . "FROM user_alliance "
                . "INNER JOIN alliances "
                . "ON alliances.aid=user_alliance.alliance "
                . "AND user_alliance.user=:user "
                . "AND user_alliance.rank<>'Follower'"
            );
            $stmt->execute([':user' => $_SESSION['id']]);
            $data['alliances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $this->twig->display('hero_recognizer.twig', $data);                
    }
}
