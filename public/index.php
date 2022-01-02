<?php

use De\Idrinth\Travian\DeffCall;
use De\Idrinth\Travian\DeffCallCreation;
use De\Idrinth\Travian\Delivery;
use De\Idrinth\Travian\HeroRecogniser;
use De\Idrinth\Travian\Login;
use De\Idrinth\Travian\Simple;
use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();

session_start();

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {
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
    $r->addRoute('GET', '/hero-check', function ($post) {
        $d = new HeroRecogniser();
        $d->run($post);
    });
    $r->addRoute('POST', '/hero-check', function ($post) {
        $d = new HeroRecogniser();
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call', function ($post) {
        $d = new DeffCallCreation(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post);
    });
    $r->addRoute('POST', '/deff-call', function ($post) {
        $d = new DeffCallCreation(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call/{uuid}', function ($post, $id) {
        $d = new DeffCall(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post, $id, '');
    });
    $r->addRoute('POST', '/deff-call/{uuid}', function ($post, $id) {
        $d = new DeffCall(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post, $id, '');
    });
    $r->addRoute('GET', '/deff-call/{uuid}/{key}', function ($post, $id, $key) {
        $d = new DeffCall(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post, $id, $key);
    });
    $r->addRoute('POST', '/deff-call/{uuid}/{key}', function ($post, $id, $key) {
        $d = new DeffCall(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post, $id, $key);
    });
    $r->addRoute('GET', '/login', function ($post) {
        $d = new Login(new PDO(
            'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ));
        $d->run($post);
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