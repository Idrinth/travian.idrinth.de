<?php

namespace De\Idrinth\Travian;

use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeffCall
{
    public function run(array $post, $id, $key=''): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        if (!Uuid::isValid($id) || !is_file(dirname(__DIR__) . '/deff/' . $id . '.json')) {
            header('Location: /deff-call', true, 307);
            return;
        }
        $json = json_decode(file_get_contents(dirname(__DIR__) . '/deff/' . $id . '.json'), true);
        $data = [
            'id' => $id,
            'key' => $key,
            'now' => time(),
            'added' => false,
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
        ];
        $json['supports'] = $json['supports'] ?? [];
        if (isset($post['scouts']) && $post['scouts'] >= 0 && isset($post['troops']) && $post['troops'] >= 0 && ($post['troops']+$post['scouts'] > 0) && isset($post['time']) && isset($post['account']) && time() < $json['target']['time']) {
            $json['supports'][] = [
                'scouts' => intval($post['scouts'], 10),
                'troops' => intval($post['troops'], 10),
                'account' => $post['account'],
                'time' => strtotime($post['time']),
                'added' => time(),
            ];
            file_put_contents(dirname(__DIR__) . '/deff/' . $id . '.json', json_encode($json));
            $data['added'] = true;
        }
        $data['supports'] = $json['supports'];
        $data['target'] = $json['target'];
        $twig->display('deff-call.twig', $data);
    }
}
