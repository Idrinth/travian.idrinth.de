<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;

class SoldierCost
{
    private static $troops = [
        'roman_legionaire' => [
            'lumber' => 120,
            'clay' => 100,
            'iron' => 150,
            'crop' => 30,
            'duration' => 26*60+40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'roman_praetorian' => [
            'lumber' => 100,
            'clay' => 130,
            'iron' => 160,
            'crop' => 70,
            'duration' => 29*60+20,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'roman_imperian' => [
            'lumber' => 150,
            'clay' => 160,
            'iron' => 210,
            'crop' => 80,
            'duration' => 32*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'roman_catapult' => [
            'lumber' => 950,
            'clay' => 1350,
            'iron' => 600,
            'crop' => 90,
            'duration' => 2*3600 + 30*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'roman_ram' => [
            'lumber' => 900,
            'clay' => 360,
            'iron' => 500,
            'crop' => 70,
            'duration' => 1*3600 + 16*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'roman_legati' => [
            'lumber' => 140,
            'clay' => 160,
            'iron' => 20,
            'crop' => 40,
            'duration' => 22*60 + 40,
            'roman_horse' => true,
            'war_engine' => false,
        ],
        'roman_imperatoris' => [
            'lumber' => 550,
            'clay' => 440,
            'iron' => 320,
            'crop' => 100,
            'duration' => 44*60,
            'roman_horse' => true,
            'war_engine' => false,
        ],
        'roman_caesaris' => [
            'lumber' => 550,
            'clay' => 640,
            'iron' => 800,
            'crop' => 180,
            'duration' => 58*60 + 40,
            'roman_horse' => true,
            'war_engine' => false,
        ],
        'teuton_clubswinger' => [
            'lumber' => 95,
            'clay' => 75,
            'iron' => 40,
            'crop' => 40,
            'duration' => 12*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_spearman' => [
            'lumber' => 145,
            'clay' => 70,
            'iron' => 85,
            'crop' => 40,
            'duration' => 18*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_axeman' => [
            'lumber' => 130,
            'clay' => 120,
            'iron' => 170,
            'crop' => 70,
            'duration' => 20*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_scout' => [
            'lumber' => 160,
            'clay' => 100,
            'iron' => 50,
            'crop' => 50,
            'duration' => 18*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_paladin' => [
            'lumber' => 370,
            'clay' => 270,
            'iron' => 290,
            'crop' => 75,
            'duration' => 40*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_knight' => [
            'lumber' => 450,
            'clay' => 515,
            'iron' => 480,
            'crop' => 80,
            'duration' => 49*60 + 20,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'teuton_ram' => [
            'lumber' => 1000,
            'clay' => 300,
            'iron' => 350,
            'crop' => 70,
            'duration' => 1*3600 + 10*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'teuton_catapult' => [
            'lumber' => 900,
            'clay' => 1200,
            'iron' => 600,
            'crop' => 60,
            'duration' => 2*3600 + 30*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'gaul_phalanx' => [
            'lumber' => 100,
            'clay' => 130,
            'iron' => 55,
            'crop' => 30,
            'duration' => 17*60 + 20,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_swordsman' => [
            'lumber' => 140,
            'clay' => 150,
            'iron' => 185,
            'crop' => 60,
            'duration' => 24*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_pathfinder' => [
            'lumber' => 170,
            'clay' => 150,
            'iron' => 20,
            'crop' => 40,
            'duration' => 22*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_thunder' => [
            'lumber' => 350,
            'clay' => 450,
            'iron' => 230,
            'crop' => 60,
            'duration' => 41*60 + 20,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_druid' => [
            'lumber' => 360,
            'clay' => 330,
            'iron' => 280,
            'crop' => 120,
            'duration' => 42*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_haeduan' => [
            'lumber' => 500,
            'clay' => 620,
            'iron' => 675,
            'crop' => 170,
            'duration' => 52*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'gaul_ram' => [
            'lumber' => 950,
            'clay' => 555,
            'iron' => 330,
            'crop' => 75,
            'duration' => 1*3600 + 23*60 + 20,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'gaul_trebuchet' => [
            'lumber' => 960,
            'clay' => 1450,
            'iron' => 630,
            'crop' => 90,
            'duration' => 2*3600 + 30*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'hun_mercenary' => [
            'lumber' => 130,
            'clay' => 80,
            'iron' => 40,
            'crop' => 40,
            'duration' => 13*60 + 30,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_bowman' => [
            'lumber' => 140,
            'clay' => 110,
            'iron' => 60,
            'crop' => 60,
            'duration' => 18*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_spotter' => [
            'lumber' => 170,
            'clay' => 150,
            'iron' => 20,
            'crop' => 40,
            'duration' => 22*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_steppe' => [
            'lumber' => 290,
            'clay' => 370,
            'iron' => 190,
            'crop' => 45,
            'duration' => 40*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_marksman' => [
            'lumber' => 320,
            'clay' => 350,
            'iron' => 330,
            'crop' => 50,
            'duration' => 41*60 + 20,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_marauder' => [
            'lumber' => 450,
            'clay' => 560,
            'iron' => 610,
            'crop' => 140,
            'duration' => 49*60 + 50,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'hun_ram' => [
            'lumber' => 1060,
            'clay' => 330,
            'iron' => 360,
            'crop' => 70,
            'duration' => 1*3600 + 13*60 + 20,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'hun_catapult' => [
            'lumber' => 950,
            'clay' => 1280,
            'iron' => 620,
            'crop' => 60,
            'duration' => 2*3600 + 30*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'egyptian_slave' => [
            'lumber' => 45,
            'clay' => 60,
            'iron' => 30,
            'crop' => 15,
            'duration' => 8*60 + 50,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_ash' => [
            'lumber' => 115,
            'clay' => 100,
            'iron' => 145,
            'crop' => 60,
            'duration' => 22*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_khopesh' => [
            'lumber' => 170,
            'clay' => 180,
            'iron' => 220,
            'crop' => 80,
            'duration' => 24*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_sopdu' => [
            'lumber' => 170,
            'clay' => 180,
            'iron' => 20,
            'crop' => 40,
            'duration' => 22*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_anhur' => [
            'lumber' => 360,
            'clay' => 330,
            'iron' => 280,
            'crop' => 120,
            'duration' => 42*60 + 40,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_resheph' => [
            'lumber' => 450,
            'clay' => 560,
            'iron' => 610,
            'crop' => 180,
            'duration' => 54*60,
            'roman_horse' => false,
            'war_engine' => false,
        ],
        'egyptian_ram' => [
            'lumber' => 995,
            'clay' => 575,
            'iron' => 340,
            'crop' => 80,
            'duration' => 1*3600 + 20*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
        'egyptian_catapult' => [
            'lumber' => 980,
            'clay' => 1510,
            'iron' => 660,
            'crop' => 100,
            'duration' => 2*3600 + 30*60,
            'roman_horse' => false,
            'war_engine' => true,
        ],
    ];
    private $twig;
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
    public function run(array $post): void
    {
        $data = [
            'inputs' => [
                'troops' => $post['troops'] ?? 0,
                'troop_type' => $post['troop_type'] ?? 'fail',
                'recruiting_level' => min(20, max(1, intval($post['recruiting_level'] ?? 0))),
                'great_recruiting_level' => min(20, max(0, intval($post['great_recruiting_level'] ?? 0))),
                'use_great_recruiting' => 1 == ($post['use_great_recruiting'] ?? 0),
                'helmet_bonus' => floatval($post['helmet_bonus'] ?? 1),
                'artefact_bonus' => floatval($post['artefact_bonus'] ?? 1),
                'alliance_bonus' => floatval($post['alliance_bonus'] ?? 1),
                'horse_trough_level' => intval($post['horse_trough_level'] ?? 0),
            ],
            'result' => [],
        ];
        if ($data['inputs']['troops'] > 0 && isset(self::$troops[$post['troop_type']])) {
            $remaining = $data['inputs']['troops'];
            $greater = $lesser = 0;
            while ($remaining > 0) {
                if ($data['inputs']['use_great_recruiting'] && !self::$troops[$data['inputs']['troop_type']]['war_engine'] && $data['inputs']['great_recruiting_level'] > 0 && $greater * pow(0.9, $data['inputs']['great_recruiting_level'] - 1) < $lesser * pow(0.9, $data['inputs']['recruiting_level'] - 1)) {
                    $greater ++;
                } else {
                    $lesser ++;
                }
                $remaining --;
            }
            $data['result']['normal_recruiting'] = $lesser;
            $data['result']['duration'] = round($data['inputs']['alliance_bonus'] * (self::$troops[$data['inputs']['troop_type']]['roman_horse'] ? 1 - $data['inputs']['horse_trough_level']/100 : 1) * $data['inputs']['helmet_bonus'] * $data['inputs']['artefact_bonus'] * ($data['inputs']['use_great_recruiting'] ? max($greater * pow(0.9, $data['inputs']['great_recruiting_level'] - 1), $lesser * pow(0.9, $data['inputs']['recruiting_level'] - 1)) : $lesser * pow(0.9, $data['inputs']['recruiting_level'] - 1)) * self::$troops[$data['inputs']['troop_type']]['duration']);
            $data['result']['iron'] = ($lesser + 3*$greater) * self::$troops[$data['inputs']['troop_type']]['iron'];
            $data['result']['crop'] = ($lesser + 3*$greater) * self::$troops[$data['inputs']['troop_type']]['crop'];
            $data['result']['clay'] = ($lesser + 3*$greater) * self::$troops[$data['inputs']['troop_type']]['clay'];
            $data['result']['lumber'] = ($lesser + 3*$greater) * self::$troops[$data['inputs']['troop_type']]['lumber'];
            $data['result']['iron_ph'] = ceil($data['result']['iron']/$data['result']['duration'] * 3600);
            $data['result']['crop_ph'] = ceil($data['result']['crop']/$data['result']['duration'] * 3600);
            $data['result']['clay_ph'] = ceil($data['result']['clay']/$data['result']['duration'] * 3600);
            $data['result']['lumber_ph'] = ceil($data['result']['lumber']/$data['result']['duration'] * 3600);
        }
        $this->twig->display('soldier-cost.twig', $data);
    }
}
