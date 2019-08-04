<?php


namespace App\Http\Controller;


use App\User\User;
use QuantFrame\Auth\AuthManager;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\View\View;
use function Amp\call;

class IndexController extends Controller
{
    /**
     * @var View
     */
    private $view;
    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * IndexController constructor.
     * @param View $view
     * @param AuthManager $authManager
     */
    public function __construct(
        View $view,
        AuthManager $authManager
    )
    {
        $this->view        = $view;
        $this->authManager = $authManager;
    }

    /**
     * @return \Amp\Promise
     */
    public function index(): \Amp\Promise
    {
        return call(function () {
            /** @var User|null $user */
            $user = yield $this->authManager->getUser();
            return new HtmlResponse($this->view->render('index.html.twig', [
                'username' => $user ? $user->username : 'Anonymous.'
            ]));
        });
    }

    /**
     * @return HtmlResponse
     */
    public function about(): HtmlResponse
    {
        return new HtmlResponse($this->view->render('about.html.twig'));
    }
}