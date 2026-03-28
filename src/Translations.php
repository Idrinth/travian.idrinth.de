<?php

namespace De\Idrinth\Travian;

use Symfony\Component\Yaml\Yaml;

class Translations
{
    public static function get(string $language): array
    {
        $dir = dirname(__DIR__) . '/translations';
        $data = (array) Yaml::parseFile($dir . '/en.yml');
        if (strlen($language) === 2 && $language !== 'en' && is_file($dir . '/' . $language . '.yml')) {
            foreach ((array) Yaml::parseFile($dir . '/' . $language . '.yml') as $key => $value) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
