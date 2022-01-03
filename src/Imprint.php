<?php

namespace De\Idrinth\Travian;

class Imprint
{
    private $twig;
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
    public function run(): void
    {
        $this->twig->display('imprint.twig');
    }
}
