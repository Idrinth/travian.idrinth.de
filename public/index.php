<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function ($post) {
        $d = new De\Idrinth\Travian\Simple();
        $d->run('home.twig');
    });
    $r->addRoute('GET', '/imprint', function ($post) {
        $d = new De\Idrinth\Travian\Simple();
        $d->run('imprint.twig');
    });
    $r->addRoute('GET', '/delivery', function ($post) {
        $d = new De\Idrinth\Travian\Delivery();
        $d->run($post);
    });
    $r->addRoute('POST', '/delivery', function ($post) {
        $d = new De\Idrinth\Travian\Delivery();
        $d->run($post);
    });
    $r->addRoute('GET', '/hero-check', function ($post) {
        $d = new De\Idrinth\Travian\HeroRecogniser();
        $d->run($post);
    });
    $r->addRoute('POST', '/hero-check', function ($post) {
        $d = new De\Idrinth\Travian\HeroRecogniser();
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call', function ($post) {
        $d = new De\Idrinth\Travian\DeffCallCreation();
        $d->run($post);
    });
    $r->addRoute('POST', '/deff-call', function ($post) {
        $d = new De\Idrinth\Travian\DeffCallCreation();
        $d->run($post);
    });
    $r->addRoute('GET', '/deff-call/{uuid}', function ($post, $id) {
        $d = new De\Idrinth\Travian\DeffCall();
        $d->run($post, $id);
    });
    $r->addRoute('POST', '/deff-call/{uuid}', function ($post, $id) {
        $d = new De\Idrinth\Travian\DeffCall();
        $d->run($post, $id);
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
    case FastRoute\Dispatcher::NOT_FOUND:
        header('', true, 404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        header('', true, 405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $handler($_POST, ...array_values($vars));
        break;
}