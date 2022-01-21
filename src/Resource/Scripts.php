<?php

namespace De\Idrinth\Travian\Resource;

use MatthiasMullie\Minify\JS;

class Scripts
{
    public function run(): void
    {
        header('Content-type: text/javascript');
        $out = [];
        foreach (array_diff(scandir(dirname(__DIR__, 2) . '/scripts'), ['.', '..']) as $script) {
            if (substr($script, -7) === '.min.js') {
                $out[] = file_get_contents(dirname(__DIR__, 2) . '/scripts/' . $script);
            } else {
                $out[] = (new JS())->add(file_get_contents(dirname(__DIR__, 2) . '/scripts/' . $script))->minify();
            }
        }
        echo 'try{' . implode("\n} catch(e) {console.log(e)}\ntry{", $out) . "\n} catch(e) {console.log(e)}";
    }
}
