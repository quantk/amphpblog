<?php

return [
    \Amp\Http\Server\Session\SessionMiddleware::class,
    \QuantFrame\Http\Middleware\OpenSessionMiddleware::class,
    \QuantFrame\Http\Middleware\CheckAuthMiddleware::class,
    \QuantFrame\Http\Middleware\RequestTimeMiddleware::class
];