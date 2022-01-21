<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;

class FAQ
{
    private $twig;
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
    public function run($post): void
    {
        $this->twig->display('faq.twig');
    }
}
