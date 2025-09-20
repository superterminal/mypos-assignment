<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Set test environment variables
$_ENV['APP_ENV'] = 'test';
$_ENV['DATABASE_URL'] = 'sqlite:///' . dirname(__DIR__) . '/var/data_test.db';
$_ENV['MAILER_DSN'] = 'null://null';
$_ENV['FROM_EMAIL'] = 'test@example.com';
