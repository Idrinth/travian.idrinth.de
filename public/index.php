<?php

use alejoluc\LazyPDO\LazyPDO;
use De\Idrinth\Travian\Application;
use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\Page\Alliance;
use De\Idrinth\Travian\Page\AttackParser;
use De\Idrinth\Travian\Page\Catcher;
use De\Idrinth\Travian\Page\DeffCall;
use De\Idrinth\Travian\Page\DeffCallCreation;
use De\Idrinth\Travian\Page\DeffCallOverview;
use De\Idrinth\Travian\Page\Delivery;
use De\Idrinth\Travian\Page\HeroRecogniser;
use De\Idrinth\Travian\Page\Home;
use De\Idrinth\Travian\Page\Imprint;
use De\Idrinth\Travian\Page\Login;
use De\Idrinth\Travian\Page\Map;
use De\Idrinth\Travian\Page\MyHero;
use De\Idrinth\Travian\Page\Profile;
use De\Idrinth\Travian\Page\ResourcePush;
use De\Idrinth\Travian\Page\ResourcePushCreation;
use De\Idrinth\Travian\Page\SoldierCost;
use De\Idrinth\Travian\Page\TroopTool;
use De\Idrinth\Travian\Page\WorldAlliances;
use De\Idrinth\Travian\Page\WorldPlayers;
use De\Idrinth\Travian\Page\Worlds;
use De\Idrinth\Travian\Resource\Bot;
use De\Idrinth\Travian\Resource\MissingTranslations;
use De\Idrinth\Travian\Resource\Ping;
use De\Idrinth\Travian\Resource\Scripts;
use De\Idrinth\Travian\Resource\Styles;
use De\Idrinth\Travian\Resource\WorldExport;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\Twig;

require_once __DIR__ . '/../vendor/autoload.php';

(new Application())
    ->register(new LazyPDO(
        'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
        $_ENV['DATABASE_USER'],
        $_ENV['DATABASE_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ]
    ))
    ->register(new Twig())
    ->register(new DistanceCalculator())
    ->register(new TravelTime())
    ->get('/', Home::class)
    ->get('/imprint', Imprint::class)
    ->get('/delivery', Delivery::class)
    ->post('/delivery', Delivery::class)
    ->get('/soldier-cost', SoldierCost::class)
    ->post('/soldier-cost', SoldierCost::class)
    ->get('/hero-check', HeroRecogniser::class)
    ->get('/hero-check/{id:int}', HeroRecogniser::class)
    ->post('/hero-check', HeroRecogniser::class)
    ->get('/deff-call', DeffCallCreation::class)
    ->post('/deff-call', DeffCallCreation::class)
    ->get('/deff-call/{id:uuid}', DeffCall::class)
    ->post('/deff-call/{id:uuid}', DeffCall::class)
    ->get('/deff-call/{id:uuid}/{key:uuid}', DeffCall::class)
    ->post('/deff-call/{id:uuid}/{key:uuid}', DeffCall::class)
    ->get('/login', Login::class)
    ->get('/profile', Profile::class)
    ->post('/profile', Profile::class)
    ->get('/styles.css', Styles::class)
    ->get('/ping', Ping::class)
    ->get('/alliance', Alliance::class)
    ->post('/alliance', Alliance::class)
    ->get('/alliance/{id:uuid}', Alliance::class)
    ->post('/alliance/{id:uuid}', Alliance::class)
    ->get('/alliance/{id:uuid}/{key:uuid}', Alliance::class)
    ->post('/troop-tool', TroopTool::class)
    ->get('/troop-tool', TroopTool::class)
    ->get('/troop-tool/{id:int}', TroopTool::class)
    ->get('/deff-call-overview', DeffCallOverview::class)
    ->get('/deff-call-overview/{world:world}', DeffCallOverview::class)
    ->get('/attack-parser', AttackParser::class)
    ->post('/attack-parser', AttackParser::class)
    ->get('/my-hero', MyHero::class)
    ->post('/my-hero', MyHero::class)
    ->get('/scripts.js', Scripts::class)
    ->get('/{world:world}.csv', WorldExport::class)
    ->get('/catcher', Catcher::class)
    ->post('/catcher', Catcher::class)
    ->get('/missing-translations/{lang:[a-z]{2}}.yml', MissingTranslations::class)
    ->get('/resource-push', ResourcePushCreation::class)
    ->post('/resource-push', ResourcePushCreation::class)
    ->get('/resource-push/{id:uuid}', ResourcePush::class)
    ->post('/resource-push/{id:uuid}', ResourcePush::class)
    ->get('/resource-push/{id:uuid}/{key:uuid}', ResourcePush::class)
    ->post('/resource-push/{id:uuid}/{key:uuid}', ResourcePush::class)
    ->get('/worlds', Worlds::class)
    ->post('/worlds', Worlds::class)
    ->get('/worlds/{world:world}/players', WorldPlayers::class)
    ->get('/worlds/{world:world}/alliances', WorldAlliances::class)
    ->get('/bot-hunt/{world:world}.{villages:[0-9]+}.{population:[0-9]+}.txt', Bot::class)
    ->get('/worlds/{world:world}/map', Map::class)
    ->run();