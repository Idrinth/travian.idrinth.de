<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;

class Home
{
    private $twig;
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
    public function run(): void
    {
        $this->twig->display('home.twig');
    }
}
