<?php

/** @noinspection PhpIncludeInspection */

use function QuantFrame\root_path;

require_once __DIR__ . '/vendor/autoload.php';
require_once root_path('framework/bootstrap.php');

return [
    'dbname'   => getenv('DB_NAME'),
    'user'     => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'host'     => getenv('DB_HOST'),
    'port'     => getenv('DB_PORT'),
    'driver'   => 'pdo_mysql',
];