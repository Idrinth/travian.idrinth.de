<?php

namespace De\Idrinth\Travian;

use De\Idrinth\Yaml\Yaml;

class MissingTranslations
{
    public function run($post, $language): void
    {
        $dir = dirname(__DIR__) . '/translations';
        $data = (array) Yaml::decodeFromFile($dir . '/en.yml');
        if (strlen($language) === 2 && $language !== 'en' && is_file($dir . '/' . $language . '.yml')) {
            foreach ((array) Yaml::decodeFromFile($dir . '/' . $language . '.yml') as $key => $value) {
                if ($value !== $data[$key]) {
                    unset($data[$key]);
                }
            }
        }
        header('Content-Type: text/plain');
        echo Yaml::encodeToString($data);
    }
}
