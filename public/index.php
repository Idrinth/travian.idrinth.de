<?php

use De\Idrinth\Travian\Alliance;
use De\Idrinth\Travian\DeffCall;
use De\Idrinth\Travian\DeffCallCreation;
use De\Idrinth\Travian\Delivery;
use De\Idrinth\Travian\HeroRecogniser;
use De\Idrinth\Travian\Login;
use De\Idrinth\Travian\Profile;
use De\Idrinth\Travian\Simple;
use De\Idrinth\Travian\SoldierCost;
use De\Idrinth\Travian\Styles;
use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();
date_default_timezone_set('UTC');
session_start();

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
    $database = new PDO(
        'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
        $_ENV['DATABASE_USER'],
        $_ENV['DATABASE_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    $r->addRoute('GET', '/', function ($post) {
        $d = new Simple();
        $d->run('home.twig');
    });
    $r->addRoute('GET', '/imprint', function ($post) {
        $d = new Simple();
        $d->run('imprint.twig');
    });
    $r->addRoute('GET', '/delivery', function ($post) {
        $d = new Delivery();
        $d->run($post);
    });
    $r->addRoute('POST', '/delivery', function ($post) {
        $d = new Delivery();
        $d->run($post);
    });
    $r->addRoute('GET', '/soldier-cost', function ($post) {
        $d = new SoldierCost();
        $d->run($post);
    });
    $r->addRoute('POST', '/soldier-cost', function ($post) {
        $d = new SoldierCost();
        $d->run($post);
    });
    $r->addRoute('GET', '/hero-check', function ($post) use (&$database) {
        $d = new HeroRecogniser($database);
        $d->run($post);
    });
    $r->addRoute('POST', '/hero-check', function ($post) use (&$database) {
        $d = new HeroRecogniser($database);
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call', function ($post) use(&$database) {
        $d = new DeffCallCreation($database);
        $d->run($post);
    });
    $r->addRoute('POST', '/deff-call', function ($post) use(&$database) {
        $d = new DeffCallCreation($database);
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call/{uuid}', function ($post, $id) use(&$database) {
        $d = new DeffCall($database);
        $d->run($post, $id, '');
    });
    $r->addRoute('POST', '/deff-call/{uuid}', function ($post, $id) use (&$database) {
        $d = new DeffCall($database);
        $d->run($post, $id, '');
    });
    $r->addRoute('GET', '/deff-call/{uuid}/{key}', function ($post, $id, $key) use (&$database) {
        $d = new DeffCall($database);
        $d->run($post, $id, $key);
    });
    $r->addRoute('POST', '/deff-call/{uuid}/{key}', function ($post, $id, $key) use (&$database) {
        $d = new DeffCall($database);
        $d->run($post, $id, $key);
    });
    $r->addRoute('GET', '/login', function ($post) use (&$database) {
        $d = new Login($database);
        $d->run($post);
    });
    $r->addRoute('GET', '/profile', function ($post) use (&$database) {
        $d = new Profile($database);
        $d->run($post);
    });
    $r->addRoute('GET', '/styles.css', function ($post) use (&$database) {
        $d = new Styles();
        $d->run();
    });
    $r->addRoute('GET', '/ping', function ($post) {});
    $r->addRoute('GET', '/alliance', function ($post) use (&$database) {
        $d = new Alliance($database);
        $d->run($post);
    });
    $r->addRoute('POST', '/alliance', function ($post) use (&$database) {
        $d = new Alliance($database);
        $d->run($post);
    });
    $r->addRoute('GET', '/alliance/{id}', function ($post, $id) use (&$database) {
        $d = new Alliance($database);
        $d->run($post, $id);
    });
    $r->addRoute('POST', '/alliance/{id}', function ($post, $id) use (&$database) {
        $d = new Alliance($database);
        $d->run($post, $id);
    });
    $r->addRoute('GET', '/alliance/{id}/{key}', function ($post, $id, $key) use (&$database) {
        $d = new Alliance($database);
        $d->run($post, $id, $key);
    });
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        header('', true, 404);
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        header('', true, 405);
        break;
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $handler($_POST, ...array_values($vars));
        break;
}