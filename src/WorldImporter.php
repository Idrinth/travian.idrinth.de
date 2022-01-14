<?php

namespace De\Idrinth\Travian;

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
    public static function register(PDO $database, string $world): void
    {
        $database
            ->prepare("INSERT IGNORE INTO world_updates (world) VALUES (:world)")
            ->execute([':world' => $world]);
        $database
            ->prepare('UPDATE world_updates SET lastUsed=:now WHERE world=:world')
            ->execute([':world' => $world, ':now' => date('Y-m-d H:i:s')]);
    }
    public function import(): void
    {
        $stmt = $this->database->prepare("SELECT world FROM world_updates WHERE updated<:today OR ISNULL(updated) AND lastUsed>:yesterday");
        $stmt->execute([':today' => date('Y-m-d H:i:s', time() - 86400), ':yesterday' => date('Y-m-d H:i:s', time() - 86400*2)]);
        $multicurl = new MultiCurl();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->database
                ->prepare('UPDATE world_updates SET updated=:now WHERE world=:world')
                ->execute([':now' => date('Y-m-d H:i:s'), ':world' => $row['world']]);
            $multicurl->addGet('https://'.$row['world'].'/map.sql')->success(function(Curl $curl) use($row) {
                $this->database->exec('TRUNCATE x_world');
                $world = $row['world'];
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
            });
        }
        $multicurl->start();
    }
}
