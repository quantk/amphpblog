<?php


namespace QuantFrame\Http\Middleware;


use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Promise;
use function Amp\call;

class RequestTimeMiddleware implements \Amp\Http\Server\Middleware
{

    /**
     * @param Request $request
     * @param RequestHandler $requestHandler
     * @psalm-suppress MixedReturnTypeCoercion
     * @return Promise<\Amp\Http\Server\Response>
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(static function () use ($request, $requestHandler) {
            $requestTime = microtime(true);

            /** @var Response $response */
            $response = yield $requestHandler->handleRequest($request);
            $value    = microtime(true) - $requestTime;
            $response->setHeader('x-request-time', (string)$value);

            return $response;
        });
    }
}