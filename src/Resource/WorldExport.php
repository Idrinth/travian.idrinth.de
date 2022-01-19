<?php

namespace De\Idrinth\Travian\Resource;

use De\Idrinth\Travian\World;
use PDO;

class WorldExport
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database=$database;
    }
    public function run($post, $world): void
    {
        World::register($this->database, $world);
        $stmt = $this->database->query("SELECT * FROM `$world`");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $world . '.csv"');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo '"' . implode('","', $row) . '"' . "\n";
        }
    }
}
