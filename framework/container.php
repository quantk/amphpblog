<?php

use DI\Container;
use DI\ContainerBuilder;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use function QuantFrame\config_path;
use function QuantFrame\is_production;
use function QuantFrame\root_path;

/** @noinspection PhpIncludeInspection */
/** @var array $config */
$config = require config_path('app.php');

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(true);

/** @noinspection PhpIncludeInspection */
/** @var array $definitions */
$definitions = require config_path('services.php');
$containerBuilder->addDefinitions($definitions);

if (is_production()) {
    $cachePath = (string)$config['cache_path'];
    $cacheDir  = $cachePath ?? root_path('var/cache/di');
    $containerBuilder->enableCompilation($cacheDir);
    $containerBuilder->writeProxiesToFile(true, root_path('var/cache/di/proxies'));
}

/** @noinspection PhpUnhandledExceptionInspection */
/** @var Container $container */
$container = $containerBuilder->build();
/** @noinspection PhpUnhandledExceptionInspection */
/** @var StorageInterface $storage */
$storage = $container->make(StorageInterface::class);

return $container;