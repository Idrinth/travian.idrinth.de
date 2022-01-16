<?php

namespace De\Idrinth\Travian;

use PDO;

class DeffCallOverview
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post, $world = ''): void
    {
        if (($_SESSION['id']??0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        $stmt2 = $this->database->prepare('SELECT DISTINCT deff_calls.world
FROM user_deff_call
INNER JOIN deff_calls ON user_deff_call.deff_call=deff_calls.aid AND deff_calls.deleted=0
WHERE user_deff_call.user=:user AND deff_calls.arrival >= :date');
        $stmt2->execute([':user' => $_SESSION['id'], ':date' => date('Y-m-d H:i:s', time() - 3600)]);
        $worlds = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if ($world) {
            $stmt = $this->database->prepare('SELECT user_deff_call.advanced, deff_calls.key, deff_calls.player,deff_calls.troops AS desiredTroops,deff_calls.scouts AS desiredScouts,deff_calls.heroes AS desiredHeroes, deff_calls.x,deff_calls.y,deff_calls.world,deff_calls.arrival, deff_calls.id,IFNULL(SUM(deff_call_supports.troops), 0) AS troops, IFNULL(SUM(deff_call_supports.scouts), 0) AS scouts, IFNULL(SUM(deff_call_supports.hero), 0) AS heroes
    FROM user_deff_call
    INNER JOIN deff_calls ON user_deff_call.deff_call=deff_calls.aid AND deff_calls.arrival >= :date AND deff_calls.deleted=0
    LEFT JOIN deff_call_supports ON deff_call_supports.deff_call=deff_calls.aid AND deff_call_supports.arrival <= deff_calls.arrival
    WHERE user_deff_call.user=:id AND deff_calls.world=:world
    GROUP BY deff_calls.aid');
            $stmt->execute([':id' => $_SESSION['id'], ':date' => date('y-m-d H:i:s', time() - 3600), ':world' => $world]);
            $data = [
                'deff_calls' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'world' => $world,
                'worlds' => $worlds,
            ];
        } else {
            $stmt = $this->database->prepare('SELECT user_deff_call.advanced, deff_calls.key, deff_calls.player,deff_calls.troops AS desiredTroops,deff_calls.scouts AS desiredScouts,deff_calls.heroes AS desiredHeroes, deff_calls.x,deff_calls.y,deff_calls.world,deff_calls.arrival, deff_calls.id,IFNULL(SUM(deff_call_supports.troops), 0) AS troops, IFNULL(SUM(deff_call_supports.scouts), 0) AS scouts, IFNULL(SUM(deff_call_supports.hero), 0) AS heroes
    FROM user_deff_call
    INNER JOIN deff_calls ON user_deff_call.deff_call=deff_calls.aid AND deff_calls.arrival >= :date AND deff_calls.deleted=0
    LEFT JOIN deff_call_supports ON deff_call_supports.deff_call=deff_calls.aid AND deff_call_supports.arrival <= deff_calls.arrival
    WHERE user_deff_call.user=:id
    GROUP BY deff_calls.aid');
            $stmt->execute([':id' => $_SESSION['id'], ':date' => date('y-m-d H:i:s', time() - 3600)]);
            $data = [
                'deff_calls' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'worlds' => $worlds,
            ];
        }
        $this->twig->display('deff-call-overview.twig', $data);
    }
}
