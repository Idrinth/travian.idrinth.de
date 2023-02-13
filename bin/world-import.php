<?php

use De\Idrinth\Travian\Command\WorldImporter;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();
date_default_timezone_set('UTC');

(new WorldImporter(new PDO(
    'mysql:host='.$_ENV['DATABASE_HOST'].';dbname=travian',
    $_ENV['DATABASE_USER'],
    $_ENV['DATABASE_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
)))->import();