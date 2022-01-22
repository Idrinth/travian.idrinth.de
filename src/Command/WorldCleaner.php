<?php

namespace De\Idrinth\Travian\Command;

use PDO;

class WorldCleaner
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function clean(): void
    {
        $stmt = $this->database->prepare("SELECT world FROM world_updates WHERE NOT ISNULL(updated) AND updated<:twoWeeksPrior");
        $stmt->execute([':twoWeeksPrior' => date('Y-m-d H:i:s', strtotime('now -2weeks'))]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $world) {
            $this->database->exec("DROP TABLE IF EXISTS `$world`");
            $this->database
                ->prepare('DELETE FROM world_updates WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM world_alliances WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM world_players WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM world_villages WHERE world=:world')
                ->execute([':world' => $world['world']]);
        }
    }
}
