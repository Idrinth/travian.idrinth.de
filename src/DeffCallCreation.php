<?php

namespace De\Idrinth\Travian;

use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeffCallCreation
{
    public function run(array $post): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        $data = [
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
            'session' => $_SESSION,
        ];
        if (isset($post['url']) && isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts'] > 0) && isset($post['time'])) {
            $uuid = Uuid::uuid6();
            $key = Uuid::uuid1();
            file_put_contents(dirname(__DIR__) . '/deff/' . $uuid . '.json', json_encode([
                'target' => [
                    'key' => $key,
                    'url' => $post['url'],
                    'scouts' => intval($post['scouts'], 10),
                    'troops' => intval($post['troops'], 10),
                    'time' => strtotime($post['time']),
                ]
            ]));
            header('Location: /deff-call/' . $uuid . '/' . $key, true, 307);
            return;
        }
        $twig->display('create-deff-call.twig', $data);
    }
}
