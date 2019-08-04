<?php

use Amp\Http\Server\Options;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Server;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use DI\Container;
use FastRoute\Dispatcher;
use Psr\Log\LoggerInterface;
use QuantFrame\Auth\AuthManager;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Handler;
use QuantFrame\Http\Response\NotFoundResponse;
use QuantFrame\Http\Response\RedirectResponse;
use function Amp\Http\Server\Middleware\stack;
use function Amp\Http\Server\redirectTo;
use function QuantFrame\app_env;
use function QuantFrame\config_path;
use function QuantFrame\root_path;

require_once __DIR__ . '/framework/bootstrap.php';

/** @var Dispatcher $dispatcher */
/** @noinspection PhpIncludeInspection */
$dispatcher = require config_path('routes.php');

/** @var Container $container */
/** @noinspection PhpIncludeInspection */
$container = require root_path('framework/container.php');

$options = getopt('p:');

$port = $options['p'] ?? 1337;
$port = (int)$port;

Amp\Loop::run(static function () use ($dispatcher, $container, $port) {
    $sockets = [
        Amp\Socket\listen("0.0.0.0:{$port}"),
        Amp\Socket\listen("[::]:{$port}"),
    ];

    $requestHandler = new CallableRequestHandler(function (Request $request) use ($dispatcher, $container, $port) {
        $routeInfo    = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $innerRequest = yield \QuantFrame\Http\Request\Request::createFromAmpRequest($request);
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                return new Response(Status::NOT_FOUND, []);
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                return new Response(Status::METHOD_NOT_ALLOWED, []);
                break;
            case FastRoute\Dispatcher::FOUND:
                /** @var Handler $handler */
                [, $handler, $vars] = $routeInfo;

                $options  = $handler->options;
                $isSecure = $options['secure'] ?? false;

                $authManager = $container->get(AuthManager::class);

                if ($isSecure && $authManager->getToken() === null) {
                    return new Success(redirectTo('/', 301));
                }

                $controllerClass = $handler->controller;
                /** @var Controller $controller */
                $controller = $container->make($controllerClass, [
                    \QuantFrame\Http\Request\Request::class => $innerRequest
                ]);
                $controller->setRequest($innerRequest);
                $controllerMethod = $handler->method;
                /** @noinspection PhpUndefinedClassInspection */
                /** @var \QuantFrame\Http\Response\Response|Promise $response */
                $response = $container->call([$controller, $controllerMethod], array_merge($vars));

                /** @noinspection PhpUndefinedClassInspection */
                if ($response instanceof Promise) {
                    $response = yield $response;
                }

                if (!($response instanceof \QuantFrame\Http\Response\Response)) {
                    throw new \RuntimeException(sprintf('Controller must return %s', \QuantFrame\Http\Response\Response::class));
                }

                if ($response instanceof NotFoundResponse) {
                    return new Response(Status::NOT_FOUND, []);
                }

                if ($response instanceof RedirectResponse) {
                    return new Response(301, array_merge(['location' => $response->targetUrl], $response->getHeaders()));
                }

                return new Response(Status::OK, $response->getHeaders(), (string)$response->getData());
            default:
                return new Response(Status::INTERNAL_SERVER_ERROR, []);
        }
    });

    /** @var array $middlewares */
    /** @noinspection PhpIncludeInspection */
    $middlewares = require config_path('middlewares.php');

    $stackArgs = [$requestHandler];
    foreach ($middlewares as $middleware) {
        $stackArgs[] = $container->make($middleware);
    }

    /** @var LoggerInterface $logger */
    $logger = $container->make(LoggerInterface::class);

    $handler = stack(...$stackArgs);

    $options = new Options();

    if (app_env() !== 'production') {
        $options = $options->withDebugMode();
    }

    $server = new Server(
        $sockets,
        $handler,
        $logger,
        $options
    );

    yield $server->start();


    // Stop the server gracefully when SIGINT is received.
    // This is technically optional, but it is best to call Server::stop().
    Amp\Loop::onSignal(SIGINT, static function (string $watcherId) use ($server) {
        Amp\Loop::cancel($watcherId);
        yield $server->stop();
    });
});