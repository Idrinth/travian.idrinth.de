<?php

use De\Idrinth\Travian\Alliance;
use De\Idrinth\Travian\DeffCall;
use De\Idrinth\Travian\DeffCallCreation;
use De\Idrinth\Travian\DeffCallOverview;
use De\Idrinth\Travian\Delivery;
use De\Idrinth\Travian\HeroRecogniser;
use De\Idrinth\Travian\Home;
use De\Idrinth\Travian\Imprint;
use De\Idrinth\Travian\Login;
use De\Idrinth\Travian\Ping;
use De\Idrinth\Travian\Profile;
use De\Idrinth\Travian\Router;
use De\Idrinth\Travian\SoldierCost;
use De\Idrinth\Travian\Styles;
use De\Idrinth\Travian\TroopTool;
use De\Idrinth\Travian\Twig;

require_once __DIR__ . '/../vendor/autoload.php';

(new Router())
    ->register(new PDO(
        'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
        $_ENV['DATABASE_USER'],
        $_ENV['DATABASE_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ))
    ->register(new Twig())
    ->get('/', Home::class)
    ->get('/imprint', Imprint::class)
    ->get('/delivery', Delivery::class)
    ->post('/delivery', Delivery::class)
    ->get('/soldier-cost', SoldierCost::class)
    ->post('/soldier-cost', SoldierCost::class)
    ->get('/hero-check', HeroRecogniser::class)
    ->get('/hero-check/{id}', HeroRecogniser::class)
    ->post('/hero-check', HeroRecogniser::class)
    ->get('/deff-call', DeffCallCreation::class)
    ->post('/deff-call', DeffCallCreation::class)
    ->get('/deff-call/{id}', DeffCall::class)
    ->post('/deff-call/{id}', DeffCall::class)
    ->get('/deff-call/{id}/{key}', DeffCall::class)
    ->post('/deff-call/{id}/{key}', DeffCall::class)
    ->get('/login', Login::class)
    ->get('/profile', Profile::class)
    ->get('/styles.css', Styles::class)
    ->get('/ping', Ping::class)
    ->get('/alliance', Alliance::class)
    ->post('/alliance', Alliance::class)
    ->get('/alliance/{id}', Alliance::class)
    ->post('/alliance/{id}', Alliance::class)
    ->get('/alliance/{id}/{key}', Alliance::class)
    ->post('/troop-tool', TroopTool::class)
    ->get('/troop-tool', TroopTool::class)
    ->get('/deff-call-overview', DeffCallOverview::class)
    ->run();