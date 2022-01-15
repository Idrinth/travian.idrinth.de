<?php

namespace De\Idrinth\Travian;

use PDO;

class Catcher
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post): void
    {
        
        $this->twig->display('catcher.twig', ['inputs' => $post]);
    }
}
