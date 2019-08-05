<?php

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\Session\Storage;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Mysql\ConnectionConfig;
use Amp\Redis\Client;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use QuantFrame\Auth\AuthManager;
use QuantFrame\Auth\Provider\DatabaseAuthProvider;
use QuantFrame\Database\ActiveRecord\Record;
use QuantFrame\Database\ActiveRecord\Storage\MysqlStorage;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use function QuantFrame\app_env;
use function QuantFrame\is_production;
use function QuantFrame\root_path;

return [
    \Amp\Sql\Pool::class => static function (/** @scrutinizer ignore-unused */ ContainerInterface $container): \Amp\Mysql\Pool {
        $config = new ConnectionConfig(
            getenv('DB_HOST') ?: '127.0.0.1',
            (int)getenv('DB_PORT') ?: 3306,
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: '',
            getenv('DB_NAME') ?: 'homepage'
        );

        /** @var \Amp\Mysql\Pool $pool */
        $pool = new \Amp\Mysql\Pool($config);
        return $pool;
    },
    Environment::class   => static function (/** @scrutinizer ignore-unused */ ContainerInterface $container): Environment {
        $appEnv    = app_env() ?? 'local';
        $loader    = new FilesystemLoader(root_path('templates'));
        $cachePath = is_production() ? root_path('var/cache/twig') : false;
        $twig      = new Environment($loader, [
            'cache' => $cachePath,
        ]);
        $twig->addFunction(new TwigFunction('truncate', static function (string $str, int $width = 400) {
            return strtok(wordwrap($str, $width, "...\n"), "\n");
        }));
        /** @psalm-suppress MissingClosureParamType */
        $twig->addFunction(new TwigFunction('count', static function ($countable) {
            /** @var Countable|array $countable */
            return count($countable);
        }));

        return $twig;
    },
    StorageInterface::class         => static function (ContainerInterface $container): StorageInterface {
        /** @var \Amp\Mysql\Pool $pool */
        $pool    = $container->get(\Amp\Sql\Pool::class);
        $storage = new MysqlStorage($pool);
        /** @noinspection PhpInternalEntityUsedInspection */
        Record::initialize($storage);

        return $storage;
    },
    Client::class                   => static function (/** @scrutinizer ignore-unused */ ContainerInterface $container): Client {
        $host = getenv('REDIS_HOST') ?? 'tcp://127.0.0.1';
        $port = getenv('REDIS_PORT') ?? 6379;
        return new Client("{$host}:{$port}");
    },
    Storage::class                  => static function (ContainerInterface $container): Storage {
        $host = getenv('REDIS_HOST') ?? 'tcp://127.0.0.1';
        $port = getenv('REDIS_PORT') ?? 6379;
        /** @var Client $client */
        $client = $container->get(Client::class);
        return new \Amp\Http\Server\Session\RedisStorage($client, new \Kelunik\RedisMutex\Mutex("{$host}:{$port}"));
    },
    AuthManager::class              => static function (ContainerInterface $container): AuthManager {
        /** @var DatabaseAuthProvider $provider */
        $provider = $container->get(DatabaseAuthProvider::class);
        return new AuthManager($provider);
    },
    \Psr\Log\LoggerInterface::class => static function (/** @scrutinizer ignore-unused */ ContainerInterface $container): LoggerInterface {
        $handler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $handler->setFormatter(new ConsoleFormatter());
        $logger = new Logger('homepage');
        $logger->pushHandler($handler);

        return $logger;
    }
];