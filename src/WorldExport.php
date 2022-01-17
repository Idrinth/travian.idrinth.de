<?php

namespace De\Idrinth\Travian;

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
        WorldImporter::register($this->database, $world);
        $stmt = $this->database->query("SELECT * FROM `$world`");
        header('Content-Type: text/csv');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo '"' . implode('","', $row) . '"' . "\n";
        }
    }
}
