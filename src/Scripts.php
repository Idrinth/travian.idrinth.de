<?php

namespace De\Idrinth\Travian;

use MatthiasMullie\Minify\JS;

class Scripts
{
    public function run(): void
    {
        header('Content-type: text/javascript');
        $out = [];
        foreach (array_diff(scandir(dirname(__DIR__) . '/scripts'), ['.', '..']) as $script) {
            if (substr($script, -7) === '.min.js') {
                $out[] = file_get_contents(dirname(__DIR__) . '/scripts/' . $script);
            } else {
                $out[] = (new JS())->add(file_get_contents(dirname(__DIR__) . '/scripts/' . $script))->minify();
            }
        }
        echo implode("\n", $out);
    }
}
