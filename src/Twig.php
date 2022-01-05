<?php

namespace De\Idrinth\Travian;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Twig extends Environment
{
    public function __construct()
    {
        parent::__construct(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $this->addFunction(new TwigFunction('floor', 'floor'));
    }

    public function display($name, $context = []): void
    {
        $context['lang'] = $_COOKIE['lang'] ?? 'en';
        $context['style'] = $_COOKIE['style'] ?? 'light';
        $context['translations'] = Translations::get($_COOKIE['lang'] ?? 'en');
        $context['session'] = $_SESSION;
        parent::display($name, $context);
    }
}
