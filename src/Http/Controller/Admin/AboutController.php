<?php


namespace App\Http\Controller\Admin;


use Amp\Promise;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\View\View;
use function Amp\call;

class AboutController extends Controller
{
    /**
     * @var View
     */
    private $view;

    /**
     * ProjectsController constructor.
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @return Promise
     */
    public function page(): Promise
    {
        return call(function () {
            return new HtmlResponse($this->view->render('admin/about.html.twig'));
        });
    }
}