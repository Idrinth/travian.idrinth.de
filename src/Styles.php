<?php

namespace De\Idrinth\Travian;

use ScssPhp\ScssPhp\Compiler;

class Styles
{
    public function run(): void
    {
        header('Content-type: text/css');
        echo (new Compiler(['cacheDir' => dirname(__DIR__).'/cache']))
            ->compileString(
                //somehow this ends up in the public dir, so we have to manually change the imports
                '@import("../styles/normalize.scss");@import("../styles/styles.scss");',
                dirname(__DIR__)
            )
            ->getCss();
    }
}
