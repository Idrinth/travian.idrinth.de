<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use PDO;

class OffPicker
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run($post)
    {
        $data = [];
        if (isset($post['world'])) {
            
        }
        $this->twig->display('off-picker.twig', $data);
    }
}