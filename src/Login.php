<?php

namespace De\Idrinth\Travian;

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
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        } else {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            $user = $provider->getResourceOwner($token);
            $_SESSION['user'] = $user->getUsername();
            $_SESSION['discriminator'] = $user->getDiscriminator();
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
            if (isset($_SESSION['redirect'])) {
                header('Location: ' . $_SESSION['redirect'], true, 303);
                return;
            }
            header('Location: /', true, 303);
        }
    }
}
