<?php

namespace De\Idrinth\Travian;

use PDO;
use Webmozart\Assert\Assert;

class World
{
    public static function getAll(PDO $database)
    {
        $stmt = $database
            ->prepare('SELECT world FROM world_updates');
        $stmt->execute();
        return array_map(function(array $row) {
            return $row['world'];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function toWorld(string $input): string
    {
        if (strpos($input, 'https://') === 0) {
            $input = substr($input, 8);
        }
        $input = explode('/', $input)[0];
        $input = strtolower($input);
        Assert::regex($input, '/^t(s[0-9]+|oc)\.x[0-9]+\.[a-z]+\.travian\.com$/');
        return $input;
    }
    public static function register(PDO $database, string $world): void
    {
        $world = self::toWorld($world);
        $database
            ->prepare("INSERT IGNORE INTO world_updates (world) VALUES (:world)")
            ->execute([':world' => $world]);
        $database
            ->prepare('UPDATE world_updates SET lastUsed=:now WHERE world=:world')
            ->execute([':world' => $world, ':now' => date('Y-m-d H:i:s')]);
    }
}
