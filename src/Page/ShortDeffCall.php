<?php

namespace De\Idrinth\Travian\Page;

class ShortDeffCall
{
    public function run(array $post, $id, $key=''): void
    {
        if ($key) {
            header("Location: /deff-call/$id/$key", true, 301);
            return;
        }
        header("Location /deff-call/$id", true, 301);
    }
}