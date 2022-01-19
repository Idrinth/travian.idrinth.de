<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use Exception;
use PDO;
use Ramsey\Uuid\Uuid;

class ResourcePush
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post, $id, $key=''): void
    {
        $data = [
            'id' => $id,
            'key' => $key,
            'now' => date('Y-m-d H:i:s'),
            'added' => false,
        ];
        if (!Uuid::isValid($id)) {
            header('Location: /resource-push', true, 303);
            return;
        }
        $stmt = $this->database->prepare("SELECT * FROM resource_pushes WHERE deleted=0 AND id=:id");
        $stmt->execute([':id' => $id]);
        $data['target'] = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $data['target']) {
            header('Location: /resource-push', true, 303);
            return;
        }
        if ($key && $key !== $data['target']['key']) {
            header('Location: /resource-push/' . $id, true, 303);
            return;
        }
        if ($data['target']['alliance'] == '0') {
            $data['target']['alliance'] = '';
        } else {
            $stmt = $this->database->prepare('SELECT alliances.name, alliances.id FROM user_alliance INNER JOIN alliances ON alliances.aid=user_alliance.alliance AND user_alliance.user=:user AND user_alliance.alliance=:alliance');
            $stmt->execute([':user' => $_SESSION['id'] ?? 0, ':alliance' => $data['target']['alliance']]);
            $data['target']['alliance'] = $stmt->fetch(PDO::FETCH_ASSOC);
            if (false === $data['target']['alliance']) {
                if ($_SESSION['id'] ?? 0 === 0) {
                    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                    header('Location: /login', true, 303);
                    return;
                }
                header('Location: /resource-push', true, 303);
                return;
            }
        }
        if (isset($post['delete']) && $post['delete']==='resource-push' && $key) {
            if (is_array($data['target']['alliance'])) {
                $this->database
                    ->prepare('UPDATE resource_pushes SET deleted=1 WHERE aid=:aid')
                    ->execute([':aid' => $data['target']['aid']]);
            } else {
                $this->database
                    ->prepare('DELETE FROM resource_pushes WHERE aid=:aid')
                    ->execute([':aid' => $data['target']['aid']]);
                $this->database
                    ->exec('DELETE FROM resource_push_supplies WHERE resource_push NOT IN(SELECT aid FROM resource_pushes)');
            }
            header('Location: /resource-push', true, 303);
            return;
        } elseif(isset($post['delete']) && $post['delete'] > 0) {
            $this->database
                ->prepare('DELETE FROM resource_push_supplies WHERE aid=:aid AND creator=:creator')
                ->execute([':aid' => $post['delete'], ':creator' => $_SESSION['id']??0]);
            header('Location: '.$_SERVER['REQUEST_URL'], true, 303);
            return;
        } elseif(isset($post['lumber']) && isset($post['clay']) && isset($post['iron']) && isset($post['crop'])) {
            $this->database
                ->prepare('INSERT INTO resource_push_supplies (resource_push,account,creator,crop,iron,clay,lumber,arrival) VALUES(:id,:account,:creator,:crop,:iron,:clay,:lumber,:arrival)')
                ->execute([
                    'id' => $data['target']['aid'],
                    'account' => $post['account'],
                    'creator' => $_SESSION['id']??0,
                    'crop' => $post['crop'],
                    'iron' => $post['iron'],
                    'clay' => $post['clay'],
                    'lumber' => $post['lumber'],
                    'arrival' => $post['date'] . ' ' . $post['time'],
                ]);
            header('Location: '.$_SERVER['REQUEST_URL'], true, 303);
            return;
        }
        $stmt = $this->database->prepare('SELECT name FROM user_world WHERE user_world.user=:user AND world=:world');
        $stmt->execute([':user' => $_SESSION['id'] ?? 0, ':world' => $data['target']['world']]);
        $data['target']['ingame'] = $stmt->fetchColumn() ?: '';
        try {
            $stmt2 = $this->database->prepare('SELECT * FROM `' . $data['target']['world'] . '` WHERE x=:x AND y=:y');
            $stmt2->execute([':x' => $data['target']['x'], ':y' => $data['target']['y']]);
            $data['worlddata'] = $stmt2->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {            
        }
        $stmt = $this->database->prepare('SELECT * FROM resource_push_supplies WHERE resource_push=:rp');
        $stmt->execute([':rp' => $data['target']['aid']]);
        $data['supplies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        World::register($this->database, $data['target']['world']);
        $this->twig->display('res-push.twig', $data);
    }
}
