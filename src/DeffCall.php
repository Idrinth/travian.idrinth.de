<?php

namespace De\Idrinth\Travian;

        ini_set('display_errors', 1);
use Ramsey\Uuid\Uuid;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeffCall
{
    public function run(array $post, $id): void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'));
        if (!Uuid::isValid($id) || !is_file(dirname(__DIR__) . '/deff/' . $id . '.json')) {
            header('Location: /deff-call', true, 307);
            return;
        }
        $json = json_decode(file_get_contents(dirname(__DIR__) . '/deff/' . $id . '.json'), true);
        $data = [
            'id' => $id,
            'now' => time(),
            'lang' => $_COOKIE['lang'] ?? 'en',
            'translations' => Translations::get($_COOKIE['lang'] ?? 'en'),
        ];
        $json['supports'] = $json['supports'] ?? [];
        if ($post['scouts'] ?? false && $post['troops'] ?? false && $post['time'] ?? false && $post['account']??false && time() < $json['target']['time']) {
            $json['supports'][] = [
                'scouts' => intval($post['scouts'], 10),
                'troops' => intval($post['troops'], 10),
                'account' => $post['account'],
                'time' => strtotime($post['time']),
                'added' => time(),
            ];
            file_put_contents(dirname(__DIR__) . '/deff/' . $id . '.json', json_encode($json));
        }
        $data['supports'] = $json['supports'];
        $data['target'] = $json['target'];
        $twig->display('deff-call.twig', $data);
    }
}
