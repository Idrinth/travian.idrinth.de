<?php

namespace De\Idrinth\Travian;

use De\Idrinth\Yaml\Yaml;

class Translations
{
    public static function get(string $language): array
    {
        $dir = dirname(__DIR__) . '/translations';
        $data = (array) Yaml::decodeFromFile($dir . '/en.yml');
        if (strlen($language) === 2 && $language !== 'en' && is_file($dir . '/' . $language . '.yml')) {
            foreach ((array) Yaml::decodeFromFile($dir . '/' . $language . '.yml') as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
