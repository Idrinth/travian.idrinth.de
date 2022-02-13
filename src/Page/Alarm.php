<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;

class Alarm
{
    private $twig;
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }
    public function run(array $post): void
    {
        $this->twig->display('alarm.twig', ['alarm' => $_GET['alarm']]);
    }
}
