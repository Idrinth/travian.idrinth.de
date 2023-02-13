<?php

namespace De\Idrinth\Travian\Command;

use Curl\Curl;
use Curl\MultiCurl;
use Exception;
use PDO;

class WorldImporter
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function import(): void
    {
        $stmt = $this->database->prepare("SELECT world,hash FROM world_updates WHERE updated<:today OR ISNULL(updated) AND lastUsed>:yesterday");
        $stmt->execute([':today' => date('Y-m-d H:i:s', time() - 86400), ':yesterday' => date('Y-m-d H:i:s', time() - 86400*2)]);
        $multicurl = new MultiCurl();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $multicurl->addGet('https://'.$row['world'].'/map.sql')->success(function(Curl $curl) use($row) {
                $world = $row['world'];
                $hash = md5($curl->response);
                $this->database
                    ->prepare('UPDATE world_updates SET updated=:now,hash=:hash WHERE world=:world')
                    ->execute([':now' => date('Y-m-d H:i:s'), ':hash' => $hash, ':world' => $world]);
                if ($row['hash'] === $hash) {
                    return;
                }
                $this->database
                    ->prepare('UPDATE world_alliances SET latest=0 WHERE world=:world')
                    ->execute([':world' => $world]);
                $this->database
                    ->prepare('UPDATE world_players SET latest=0 WHERE world=:world')
                    ->execute([':world' => $world]);
                $this->database
                    ->prepare('UPDATE world_villages SET latest=0 WHERE world=:world')
                    ->execute([':world' => $world]);
                $this->database->exec('TRUNCATE x_world');
                foreach(explode("\n", $curl->response) as $row) {
                    if ($row) {
                        $this->database->exec(str_replace([',FALSE,', ',TRUE,'], [',0,', ',1,'], $row));
                    }
                }
                try {
                    $this->database->exec('DROP TABLE `' . $world . '`');
                } catch (Exception $e) {
                    
                }
                $this->database->exec('CREATE TABLE `' . $world . '` LIKE x_world');
                $this->database->exec('INSERT INTO `' . $world . '` SELECT * FROM x_world');
                $this->database->exec('TRUNCATE x_world');
                foreach ($this->database->query("SELECT DISTINCT alliance_id,alliance_name FROM `$world` WHERE alliance_id<>0")->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $stmt = $this->database->prepare('SELECT aid,name FROM world_alliances WHERE id=:id AND world=:world');
                    $stmt->execute([':world' => $world, ':id' => $row['alliance_id']]);
                    list($id, $name) = $stmt->fetch(PDO::FETCH_NUM);
                    if (!$id || $row['alliance_name'] !== $name) {
                        $this->database
                            ->prepare('INSERT INTO world_alliances (id,world,name,`from`,`until`,latest) VALUES(:id,:world,:name,:today,:today,1)')
                            ->execute([':name' => $row['alliance_name'], ':id' => $row['alliance_id'], ':world' => $world, ':today' => date('Y-m-d')]);
                    } else {
                        $this->database
                            ->prepare('UPDATE world_alliances SET until=:today,latest=1 WHERE aid=:id')
                            ->execute([':id' => $id, ':today' => date('Y-m-d')]);
                    }
                }
                foreach ($this->database->query("SELECT DISTINCT player_id,player_name,alliance_id FROM `$world`")->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $stmt = $this->database->prepare('SELECT aid,name,alliance FROM world_players WHERE id=:id AND world=:world');
                    $stmt->execute([':world' => $world, ':id' => $row['player_id']]);
                    list($id, $name, $alliance) = $stmt->fetch(PDO::FETCH_NUM);
                    if (!$id || $row['player_name'] !== $name || $alliance !== $row['alliance_id']) {
                        $this->database
                            ->prepare('INSERT INTO world_players (id,alliance,world,name,`from`,`until`,latest) VALUES(:id,:alliance,:world,:name,:today,:today,1)')
                            ->execute([':name' => $row['player_name'], ':id' => $row['player_id'], ':alliance' => $row['alliance_id'], ':world' => $world, ':today' => date('Y-m-d')]);
                    } else {
                        $this->database
                            ->prepare('UPDATE world_players SET until=:today,latest=1 WHERE aid=:id')
                            ->execute([':id' => $id, ':today' => date('Y-m-d')]);
                    }
                }
                foreach ($this->database->query("SELECT DISTINCT tribe,village_id,player_id,population,village_name,x,y FROM `$world`")->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    var_dump($row['tribe']);
                    $this->database
                        ->prepare('INSERT INTO world_villages (tribe,population,id,x,y,world,name,day,player,latest) VALUES(:tribe,:population,:id,:x,:y,:world,:name,:today,:player,1)')
                        ->execute([
                            ':tribe' => [1 => 'roman', 2 => 'teuton', 3 => 'gaul', 5 => 'natar', 6 => 'egyptian', 7 => 'hun'][intval($row['tribe'], 10)],
                            ':population' => $row['population'],
                            ':name' => $row['village_name'],
                            ':x' => $row['x'],
                            ':y' => $row['y'],
                            ':id' => $row['village_id'],
                            ':player' => $row['player_id'],
                            ':world' => $world,
                            ':today' => date('Y-m-d')
                        ]);
                }
            });
        }
        $multicurl->start();
    }
}
