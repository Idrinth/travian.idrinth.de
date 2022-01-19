<?php

use alejoluc\LazyPDO\LazyPDO;
use De\Idrinth\Travian\Alliance;
use De\Idrinth\Travian\AttackParser;
use De\Idrinth\Travian\Catcher;
use De\Idrinth\Travian\DeffCall;
use De\Idrinth\Travian\DeffCallCreation;
use De\Idrinth\Travian\DeffCallOverview;
use De\Idrinth\Travian\Delivery;
use De\Idrinth\Travian\DistanceCalculator;
use De\Idrinth\Travian\HeroRecogniser;
use De\Idrinth\Travian\Home;
use De\Idrinth\Travian\Imprint;
use De\Idrinth\Travian\Login;
use De\Idrinth\Travian\MissingTranslations;
use De\Idrinth\Travian\MyHero;
use De\Idrinth\Travian\Ping;
use De\Idrinth\Travian\Profile;
use De\Idrinth\Travian\ResourcePush;
use De\Idrinth\Travian\ResourcePushCreation;
use De\Idrinth\Travian\Router;
use De\Idrinth\Travian\Scripts;
use De\Idrinth\Travian\SoldierCost;
use De\Idrinth\Travian\Styles;
use De\Idrinth\Travian\TravelTime;
use De\Idrinth\Travian\TroopTool;
use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\WorldExport;
use De\Idrinth\Travian\Worlds;

require_once __DIR__ . '/../vendor/autoload.php';

(new Router())
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
    ->run();