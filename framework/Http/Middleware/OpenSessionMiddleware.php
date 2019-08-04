<?php


namespace QuantFrame\Http\Middleware;


use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Session\Session;
use Amp\Promise;
use function Amp\call;

class OpenSessionMiddleware implements Middleware
{

    /**
     * @param Request $request
     * @param RequestHandler $requestHandler
     * @psalm-suppress MixedReturnTypeCoercion
     * @return Promise<\Amp\Http\Server\Response>
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            /** @var Session $session */
            $session = $request->getAttribute(Session::class);
            yield $session->open();
            $session->set('open', 'true');
            yield $session->save();

            return yield $requestHandler->handleRequest($request);
        });
    }
}