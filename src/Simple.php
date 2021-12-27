<?php

namespace De\Idrinth\Travian;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Simple {

    public function run(string $template): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $twig->display($template, [
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en')
        ]);
    }

}
