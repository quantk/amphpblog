<?php

use Symfony\Component\Console\Application;
use function QuantFrame\root_path;

require __DIR__ . '/vendor/autoload.php';

/** @var \DI\Container $container */
$container = require root_path('framework/bootstrap.php');

/** @var array $consoleSettings */
$consoleSettings = require \QuantFrame\config_path('console.php');
$commands        = $consoleSettings['commands'] ?? [];

$application = new Application();

$application->add(new \Doctrine\Migrations\Tools\Console\Command\GenerateCommand());
$application->add(new \Doctrine\Migrations\Tools\Console\Command\MigrateCommand());

foreach ($commands as $command) {
    $application->add($container->make($command));
}

$application->run();