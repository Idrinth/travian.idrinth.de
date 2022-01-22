<?php

namespace De\Idrinth\Travian\Page;

use PDO;
use Wohali\OAuth2\Client\Provider\Discord;

class Login
{
    private $database;
    public function __construct(PDO $database)
    {
        $this->database = $database;
    }
    public function run(array $post): void
    {
        if (($_SESSION['id'] ?? 0) !== 0) {
            header('Location: /profile', true, 303);
            return;
        }
        $provider = new Discord([
            'clientId' => $_ENV['DISCORD_CLIENT_ID'],
            'clientSecret' => $_ENV['DISCORD_CLIENT_SECRET'],
            'redirectUri' => 'https://travian.idrinth.de/login'
        ]);
        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl(['scope' => 'identify']);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl, true, 303);
            return;
        }
        if (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            header('Location: /login', true, 303);
            return;
        }
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        $user = $provider->getResourceOwner($token);
        $_SESSION['user'] = $user->getUsername();
        $_SESSION['discriminator'] = $user->getDiscriminator();
        $stmt = $this->database->prepare('SELECT aid FROM users WHERE discord_id=:discordId');
        $stmt->execute([
                ':discordId' => $user->getId(),
            ]);
        $_SESSION['id'] = intval($stmt->fetchColumn(), 10);
        if ($_SESSION['id'] === 0) {
            $this->database
                ->prepare("INSERT INTO users (discord_id, name, discriminator) VALUES (:discordId, :name, :discriminator) ON DUPLICATE KEY UPDATE name=name,discriminator=discriminator")
                ->execute([
                    ':discordId' => $user->getId(),
                    ':name' => $user->getUsername(),
                    ':discriminator' => $user->getDiscriminator(),
                ]);
            $stmt = $this->database
                ->prepare("SELECT aid FROM users WHERE discord_id=:discordId");
            $stmt->execute([
                ':discordId' => $user->getId(),
            ]);
            $_SESSION['id'] = intval($stmt->fetchColumn(), 10);
        } else {
            $this->database
                ->prepare("UPDATE users SET name=:name, discriminator=:discriminator WHERE discord_id=:discordId")
                ->execute([
                    ':discordId' => $user->getId(),
                    ':name' => $user->getUsername(),
                    ':discriminator' => $user->getDiscriminator(),
                ]);
        }
        if (isset($_SESSION['redirect'])) {
            header('Location: ' . $_SESSION['redirect'], true, 303);
            return;
        }
        header('Location: /', true, 303);
    }
}
