<?php


namespace QuantFrame\Http\Middleware;


use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Session\Session;
use Amp\Promise;
use QuantFrame\Auth\AuthManager;
use Twig\Environment;
use Twig\TwigFunction;
use function Amp\call;

class CheckAuthMiddleware implements Middleware
{
    /**
     * @var AuthManager
     */
    private $authManager;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * CheckAuthMiddleware constructor.
     * @param AuthManager $authManager
     * @param Environment $environment
     */
    public function __construct(AuthManager $authManager, Environment $environment)
    {
        $this->authManager = $authManager;
        $this->environment = $environment;
    }


    /**
     * @param Request $request
     * @param RequestHandler $requestHandler
     *
     * @return Promise<\Amp\Http\Server\Response>
     */
    public function handleRequest(Request $request, RequestHandler $requestHandler): Promise
    {
        return call(function () use ($request, $requestHandler) {
            /** @var Session $ampSession */
            $ampSession = $request->getAttribute(Session::class);
            $session    = new \QuantFrame\Http\Session($ampSession);
            $this->authManager->setSession($session);
            yield $this->authManager->initialize();
            $authManager = $this->authManager;
            try {
                $this->environment->addFunction(new TwigFunction('is_logged_in', function () use ($authManager) {
                    return $authManager->isLoggedIn();
                }));
            } catch (\Throwable $e) {
            }


            return $requestHandler->handleRequest($request);
        });
    }
}