<?php

namespace De\Idrinth\Travian;

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use function FastRoute\simpleDispatcher;

class Application
{
    private $routes=[];
    private $singletons=[];
    private const LIFETIME=86400;
    public function __construct()
    {
        Dotenv::createImmutable(dirname(__DIR__))->load();
        date_default_timezone_set('UTC');
        ini_set('session.gc_maxlifetime', self::LIFETIME);
        session_set_cookie_params(self::LIFETIME, '/', 'travian.idrinth.de', true, true);
        session_start();
        setcookie('lang', $_COOKIE['lang']??'en', [
            'expires' => time() +self::LIFETIME,
            'path' => '/',
            'domain' => 'travian.idrinth.de',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Strict'
        ]);
        setcookie('style', $_COOKIE['style']??'light', [
            'expires' => time() +self::LIFETIME,
            'path' => '/',
            'domain' => 'travian.idrinth.de',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Strict'
        ]);
    }

    public function register(object $singleton): self
    {
        $rf = new ReflectionClass($singleton);
        $this->singletons[$rf->getName()] = $singleton;
        while ($rf = $rf->getParentClass()) {
            $this->singletons[$rf->getName()] = $singleton;
        }
        return $this;
    }

    public function get(string $path, string $class): self
    {
        return $this->add('GET', $path, $class);
    }

    public function post(string $path, string $class): self
    {
        return $this->add('POST', $path, $class);
    }
    private function add(string $method, string $path, string $class): self
    {
        $path = str_replace(':int}', ':[0-9]+}', $path);
        $path = str_replace(':uuid}', ':[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}}', $path);
        $path = str_replace(':world}', ':ts[0-9]+\.x[0-9]+\.[a-z]+\.travian\.com}', $path);
        $this->routes[$path] = $this->routes[$path] ?? [];
        $this->routes[$path][$method] = $class;
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
                echo "404 NOT FOUND";
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                header('', true, 405);
                echo "405 METHOD NOT ALLOWED";
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
                try {
                    $obj->run($_POST, ...array_values($vars));
                } catch (Throwable $t) {
                    header('', true, 500);
                    echo "Failed with {$t->getMessage()}";
                }
                break;
        }
    }
}
