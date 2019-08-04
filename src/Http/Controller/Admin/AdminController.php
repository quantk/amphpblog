<?php


namespace App\Http\Controller\Admin;


use Amp\Promise;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\Http\Response\Response;
use QuantFrame\View\View;
use function Amp\call;

class AdminController extends Controller
{
    /**
     * @var View
     */
    private $view;

    /**
     * AdminController constructor.
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return Promise<Response>
     */
    public function index(): Promise
    {
        return call(function () {
            return new HtmlResponse($this->view->render('admin/index.html.twig'));
        });
    }
}