<?php

namespace De\Idrinth\Travian;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
    public function run(array $post): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $data = [
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'inputs' => [
                'url' => $post['url'] ?? ''
            ],
            'results' => [],
            'session' => $_SESSION,
        ];
        if ($data['inputs']['url']) {
            $ids = substr($data['inputs']['url'], strpos($data['inputs']['url'], '/body/') + 6, 68);
            $data['results']['horse'] = self::$horse[hexdec(substr($ids, 40, 2))];
            $data['results']['left_hand'] = self::$left[hexdec(substr($ids, 56, 2))];
            $data['results']['right_hand'] = self::$right[hexdec(substr($ids, 52, 2))];
            $data['results']['shoes'] = self::$shoes[hexdec(substr($ids, 64, 2))];
            $data['results']['armor'] = self::$armor[hexdec(substr($ids, 60, 2))];
            $data['results']['helmet'] = self::$helmet[hexdec(substr($ids, 49, 1))];
        }
        $twig->display('hero_recognizer.twig', $data);                
    }
}
