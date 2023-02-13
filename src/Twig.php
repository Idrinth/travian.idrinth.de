<?php

namespace De\Idrinth\Travian;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use voku\helper\HtmlMin;
use PDO;

class Twig extends Environment
{
    private $database;

    public function __construct(PDO $database)
    {
        parent::__construct(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $this->addFunction(new TwigFunction('floor', 'floor'));
        $this->addFunction(new TwigFunction('round', 'round'));
        $this->addFunction(new TwigFunction('ceil', 'ceil'));
        $this->addFunction(new TwigFunction('strtotime', 'strtotime'));
        $this->addFunction(new TwigFunction('num', function($value) {
            return number_format(round($value * 10)/10, 1);
        }));
        $this->database = $database;
    }

    public function display($name, $context = []): void
    {
        $context['lang'] = $_COOKIE['lang'] ?? 'en';
        $context['style'] = $_COOKIE['style'] ?? 'light';
        $context['world'] = $_COOKIE['world'] ?? 'world';
        $context['translations'] = Translations::get($_COOKIE['lang'] ?? 'en');
        $context['session'] = $_SESSION;
        $context['worlds'] = World::getAll($this->database);
        echo (new HtmlMin())->minify(parent::render($name, $context));
    }
}
