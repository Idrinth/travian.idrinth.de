<?php

namespace De\Idrinth\Travian;

use Wohali\OAuth2\Client\Provider\Discord;

class Login
{
    public function run(array $post): void
    {
        $provider = new Discord([
            'clientId' => $_ENV['DISCORD_CLIENT_ID'],
            'clientSecret' => $_ENV['DISCORD_CLIENT_SECRET'],
            'redirectUri' => 'https://travian.idrinth.de/login'
        ]);
        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl(['scope' => 'identify']);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl, true, 307);
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
            header('Location: /', true, 307);
        }
    }
}
