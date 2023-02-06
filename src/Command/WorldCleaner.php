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
        $stmt = $this->database->prepare("SELECT world FROM world_updates WHERE (NOT ISNULL(updated) AND updated<:twoWeeksPrior) OR (ISNULL(update) AND lastUsed<:twoWeeksPrior)");
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
            $this->database
                ->prepare('DELETE FROM alliances WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM user_alliance WHERE alliance NOT IN(SELECT aid FROM alliances)')
                ->execute();
            $this->database
                ->prepare('DELETE FROM user_world WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM my_hero WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM resource_pushes WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM deff_calls WHERE world=:world')
                ->execute([':world' => $world['world']]);
            $this->database
                ->prepare('DELETE FROM troops WHERE world=:world')
                ->execute([':world' => $world['world']]);
        $this->database
            ->prepare('DELETE FROM troop_updates WHERE world=:world')
            ->execute([':world' => $world['world']]);
        }
        $this->database
            ->prepare('DELETE FROM alliances WHERE aid NOT IN (SELECT alliance FROM user_alliance)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM world_players WHERE until<:year')
            ->execute([':year' => date('Y-m-d H:i:s', strtotime('now -1year'))]);
        $this->database
            ->prepare('DELETE FROM world_alliances WHERE until<:year')
            ->execute([':year' => date('Y-m-d H:i:s', strtotime('now -1year'))]);
        $this->database
            ->prepare('DELETE FROM deff_call_supplies WHERE deff_call NOT IN (SELECT aid FROM deff_calls)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM deff_call_supports WHERE deff_call NOT IN (SELECT aid FROM deff_calls)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM alliance_server WHERE alliance NOT IN (SELECT aid FROM alliances)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM alliance_attacks WHERE alliance NOT IN (SELECT aid FROM alliances)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM attack_plan WHERE alliance NOT IN (SELECT aid FROM alliances)')
            ->execute();
        $this->database
            ->prepare('DELETE FROM attack_plan_unit WHERE attack_plan NOT IN (SELECT aid FROM attack_plan)')
            ->execute();
    }
}
