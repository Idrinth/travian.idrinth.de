<?php

namespace De\Idrinth\Travian\Page;

class ShortAlliance
{
    public function run(array $post, $id, $key=''): void
    {
        if ($key) {
            header("Location: /alliance/$id/$key", true, 301);
            return;
        }
        header("Location: /alliance/$id", true, 301);
    }
}
