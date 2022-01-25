<?php

namespace De\Idrinth\Travian\Page;

use De\Idrinth\Travian\Twig;
use De\Idrinth\Travian\World;
use PDO;
use Ramsey\Uuid\Uuid;

class Alliance
{
    private $database;
    private $twig;
    public function __construct(PDO $database, Twig $twig)
    {
        $this->database = $database;
        $this->twig = $twig;
    }
    public function run(array $post, $id = '', $key=''): void
    {
        if (($_SESSION['id'] ?? 0) === 0) {
            header('Location: /login', true, 303);
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return;
        }
        if ($id && $key) {
            $stmt = $this->database->prepare("SELECT aid FROM alliances WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $alliance = $stmt->fetchColumn();
            if (!$alliance) {
                header('Location: /profile', true, 303);
                return;
            }
            $this->database
                ->prepare("INSERT IGNORE INTO user_alliance (user, alliance) VALUES (:user, :alliance)")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':alliance' => $alliance
                ]);
            header('Location: /alliance/'.$id, true, 303);
            return;
        }
        if ($id) {
            $stmt = $this->database->prepare("SELECT * FROM alliances WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $alliance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (false === $alliance) {
                header('Location: /profile', true, 303);
                return;
            }
            World::register($this->database, $alliance['world']);
            $stmt = $this->database->prepare("SELECT user_alliance.* FROM user_alliance WHERE alliance=:alliance AND user=:user");
            $stmt->execute([':alliance' => $alliance['aid'], ':user' => $_SESSION['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (isset($post['regen-key']) && in_array($user['rank'], ['High Council', 'Creator'], true)) {
                $this->database
                    ->prepare("UPDATE alliances SET `key`=:key WHERE id=:id")
                    ->execute([':id' => $id, ':key' => Uuid::uuid4()]);
            } elseif (isset($post['user']) && isset($post['rank']) && $post['user'] != $_SESSION['id'] && in_array($user['rank'], ['High Council', 'Creator'], true)) {
                if ($post['rank'] === 'Kick') {
                    $this->database
                        ->prepare("DELETE FROM user_alliance WHERE alliance=:alliance AND user=:user")
                        ->execute([':alliance' => $alliance['aid'], ':user' => $post['user']]);
                } elseif ($post['rank'] !== 'Creator') {
                    $this->database
                        ->prepare("UPDATE user_alliance SET `rank`=:rank WHERE alliance=:alliance AND user=:user")
                        ->execute([':alliance' => $alliance['aid'], ':user' => $post['user'], ':rank' => $post['rank']]);
                }
            }
            $stmt = $this->database->prepare('SELECT user_world.name,user_alliance.*,my_hero.resources,my_hero.off_bonus,my_hero.deff_bonus,my_hero.fighting_strength, users.aid, users.name as discord, users.discriminator
FROM user_alliance
INNER JOIN users ON users.aid=user_alliance.user
LEFT JOIN user_world ON users.aid=user_world.user AND user_world.world=:world
LEFT JOIN my_hero ON my_hero.user=user_alliance.user AND my_hero.world=:world
WHERE alliance=:alliance');
            $stmt->execute([':alliance' => $alliance['aid'], ':world' => $alliance['world']]);
            $stmt2 = $this->database->prepare("SELECT deff_calls.player, deff_calls.id, deff_calls.arrival, deff_calls.world, deff_calls.x, deff_calls.y, deff_calls.key FROM deff_calls WHERE deleted=0 AND alliance=:alliance");
            $stmt2->execute([':alliance' => $alliance['aid']]);
            $stmt3 = $this->database->prepare("SELECT * FROM hero WHERE alliance=:alliance");
            $stmt3->execute([':alliance' => $alliance['aid']]);
            $stmt4 = $this->database->prepare("SELECT
	IFNULL(SUM(deff_call_supports.troops/1000), 0)
	+ IFNULL(SUM(deff_call_supports.scouts/500), 0)
	+ IFNULL(SUM(deff_call_supports.hero/5), 0)
	+ COUNT(DISTINCT deff_call_supports.deff_call)*2.5
	+ COUNT(DISTINCT hero_updates.`date`)*0.5
	+ COUNT(DISTINCT troop_updates.date) 
	+ IFNULL(SUM(deff_call_supplies.grain), 0) * 0.0001
	+ COUNT(DISTINCT resource_pushes.aid) * 0.5
	+ (IFNULL(SUM(resource_push_supplies.lumber), 0)+IFNULL(SUM(resource_push_supplies.clay), 0)+IFNULL(SUM(resource_push_supplies.iron), 0)+IFNULL(SUM(resource_push_supplies.crop), 0)) * 0.001
	 AS activity,
	user_alliance.`user`,
	IFNULL(SUM(deff_call_supports.troops), 0) AS troops,
	IFNULL(SUM(deff_call_supports.scouts), 0) AS scouts,
	COUNT(DISTINCT deff_call_supports.deff_call) AS deffCalls,
	COUNT(DISTINCT hero_updates.`date`) AS heroes,
	IFNULL(SUM(deff_call_supports.hero), 0) AS heroesDeff,
	COUNT(DISTINCT troop_updates.date) AS troopUpdate,
	IFNULL(SUM(deff_call_supplies.grain), 0) AS grain,
	COUNT(DISTINCT resource_pushes.aid) AS pushes,
	IFNULL(SUM(resource_push_supplies.lumber), 0)+IFNULL(SUM(resource_push_supplies.clay), 0)+IFNULL(SUM(resource_push_supplies.iron), 0)+IFNULL(SUM(resource_push_supplies.crop), 0) AS resources
FROM user_alliance
INNER JOIN alliances ON alliances.aid=user_alliance.alliance

LEFT JOIN deff_calls ON deff_calls.alliance=:alliance AND deff_calls.arrival >= :cutoffDate
LEFT JOIN deff_call_supports ON deff_calls.aid=deff_call_supports.deff_call AND deff_call_supports.creator=user_alliance.user
LEFT JOIN deff_call_supplies ON deff_calls.aid=deff_call_supplies.deff_call AND deff_call_supplies.user=user_alliance.user

LEFT JOIN hero ON hero.alliance=:alliance
LEFT JOIN hero_updates ON hero.aid=hero_updates.hero AND hero_updates.user=user_alliance.user AND hero_updates.date >= :cutoffDate

LEFT JOIN troop_updates ON troop_updates.user=user_alliance.user AND troop_updates.date >= :cutoffDate AND troop_updates.world=alliances.world

LEFT JOIN resource_pushes ON resource_pushes.alliance=alliances.aid AND resource_pushes.arrival >= :cutoffDate
LEFT JOIN resource_push_supplies ON resource_push_supplies.resource_push=resource_pushes.aid AND resource_push_supplies.creator=user_alliance.user

WHERE user_alliance.alliance=:alliance

GROUP BY user_alliance.`user`");
            $stmt4->execute([':alliance' => $alliance['aid'], ':cutoffDate' => date('Y-m-d', time() - 86400 * 7)]);
            $stmt5 = $this->database->prepare("SELECT
	user_alliance.user,
	SUM(troops.soldier1+troops.soldier2+troops.soldier3+troops.soldier4+troops.soldier5+troops.soldier6) AS troops,
	SUM(troops.settler) AS settlers,
	SUM(troops.chief) AS chiefs,
	SUM(troops.ram) AS rams,
	SUM(troops.catapult) AS catapults,
        MAX(troops.updated) AS updated
FROM alliances
INNER JOIN user_alliance ON user_alliance.alliance=alliances.aid
INNER JOIN troops ON user_alliance.user=troops.user AND alliances.world=troops.world
WHERE alliances.aid=:aid
GROUP BY user_alliance.user,alliances.aid");
            $stmt5->execute([':aid' => $alliance['aid']]);
            $stmt6 = $this->database->prepare("SELECT *
FROM resource_pushes
WHERE alliance=:aid
AND deleted=0");
            $stmt6->execute([':aid' => $alliance['aid']]);
            $stmt7 = $this->database->prepare('SELECT *
FROM attack_plan
WHERE alliance=:aid');
            $stmt7->execute([':aid' => $alliance['aid']]);
            $this->twig->display('alliance.twig', [
                'alliance' => $alliance,
                'players' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'deff_calls' => $stmt2->fetchAll(PDO::FETCH_ASSOC),
                'heroes' => $stmt3->fetchAll(PDO::FETCH_ASSOC),
                'activity' => $stmt4->fetchAll(PDO::FETCH_ASSOC),
                'troops' => $stmt5->fetchAll(PDO::FETCH_ASSOC),
                'pushes' => $stmt6->fetchAll(PDO::FETCH_ASSOC),
                'attacks' => $stmt7->fetchAll(PDO::FETCH_ASSOC),
            ]);
            return;
        }
        if (isset($post['name']) && isset($post['world'])) {
            $id = Uuid::uuid6();
            $this->database
                ->prepare("INSERT INTO alliances (id, name, world, `key`) VALUES (:id, :name, :world, :key)")
                ->execute([
                    ':id' => $id,
                    ':name' => $post['name'],
                    ':world' => World::toWorld($post['world']),
                    ':key' => Uuid::uuid4(),
                ]);
            $this->database
                ->prepare("INSERT INTO user_alliance (user, alliance, rank) VALUES (:user, :alliance, 'Creator')")
                ->execute([
                    ':user' => $_SESSION['id'],
                    ':alliance' => $this->database->lastInsertId()
                ]);
            header('Location: /profile', true, 303);
            return;
        }
        $this->twig->display('alliance-create.twig');
    }
}
