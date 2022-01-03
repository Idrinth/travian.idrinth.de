<?php

namespace De\Idrinth\Travian;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use ReflectionClass;
use ReflectionMethod;
use function FastRoute\simpleDispatcher;

class Router
{
    private $routes=[];
    private $singletons=[];
    
    public function __construct()
    {
        Dotenv::createImmutable(dirname(__DIR__))->load();
        date_default_timezone_set('UTC');
        session_start();
        setcookie('lang', $_COOKIE['lang']??'en', 0, '/', 'travian.idrinth.de', true, false);
    }

    public function register(object $singleton): self
    {
        $this->singletons[get_class($singleton)] = $singleton;
        return $this;
    }

    public function get(string $path, string $class): self
    {
        $this->routes[$path] = $this->routes[$path] ?? [];
        $this->routes[$path]['GET'] = $class;
        return $this;
    }

    public function post(string $path, string $class): self
    {
        $this->routes[$path] = $this->routes[$path] ?? [];
        $this->routes[$path]['GET'] = $class;
        return $this;
    }

    public function run(): void
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $path => $data) {
                foreach($data as $method => $func) {
                    $r->addRoute($method, $path, $func);
                }
            }
        });
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
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
                $rf = new ReflectionClass($handler);
                $args = [];
                $constructor = $rf->getConstructor();
                if ($constructor instanceof ReflectionMethod) {
                    foreach ($constructor->getParameters() as $parameter) {
                        $args[] = $this->singletons[$parameter->getType()->getName()];
                    }
                }
                $obj = new $handler(...$args);
                $obj->run($_POST, ...array_values($vars));
                break;
        }
    }
}
