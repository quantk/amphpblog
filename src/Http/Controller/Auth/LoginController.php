<?php


namespace App\Http\Controller\Auth;


use Amp\Promise;
use Psr\Log\LoggerInterface;
use QuantFrame\Auth\AuthManager;
use QuantFrame\Auth\Credentials;
use QuantFrame\Auth\Token\UserToken;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\Http\Response\RedirectResponse;
use QuantFrame\Http\Response\Response;
use QuantFrame\View\View;
use function Amp\call;

class LoginController extends Controller
{
    /**
     * @var AuthManager
     */
    private $authManager;
    /**
     * @var View
     */
    private $view;
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * LoginController constructor.
     * @param AuthManager $authManager
     * @param View $view
     * @param LoggerInterface $logger
     */
    public function __construct(
        AuthManager $authManager,
        View $view,
        LoggerInterface $logger
    )
    {
        $this->authManager = $authManager;
        $this->view        = $view;
        $this->logger      = $logger;
    }

    /**
     * @return Promise<Response>
     */
    public function logout(): Promise
    {
        return call(function () {
            yield $this->authManager->logout();
            return new RedirectResponse('/', [
                'cache-control' => 'no-cache'
            ]);
        });
    }

    /**
     * @return Promise<Response>
     */
    public function login(): Promise
    {
        return call(function () {
            if ($this->authManager->getToken() instanceof UserToken) {
                return new RedirectResponse('/');
            }

            $request = $this->request;
            if ($request->method === 'POST') {
                /** @var string $username */
                $username = $request->form->getValue('username');
                /** @var string $password */
                $password = $request->form->getValue('password');

                $token = yield $this->authManager->authenticate(new Credentials($username, $password));
                return new RedirectResponse('/');
            }

            return new HtmlResponse($this->view->render('login.html.twig'));
        });
    }
}