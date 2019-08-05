<?php


namespace App\Http\Controller\Admin;


use Amp\Promise;
use App\Meta\Meta;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\Http\Response\RedirectResponse;
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
    public function edit(): Promise
    {
        return call(function () {
            /** @var Meta|null $meta */
            $meta = yield Meta::find('about');
            if ($meta === null) {
                $meta        = Meta::create();
                $meta->key   = 'about';
                $meta->value = ['text' => ''];
                yield $meta->save();
            }

            if ($this->request->method === 'POST') {
                $text = $this->request->form->getValue('text');

                $meta->value['text'] = $text;
                yield $meta->save();
                return new RedirectResponse('/admin/about/edit');
            }

            if (!isset($meta->value['text'])) {
                $meta->value['text'] = '';
            }

            return new HtmlResponse($this->view->render('admin/about/about_edit.html.twig', [
                'about' => $meta->value['text']
            ]));
        });
    }

    /**
     * @return Promise
     */
    public function page(): Promise
    {
        return call(function () {
            /** @var Meta|null $aboutMeta */
            $aboutMeta = yield Meta::find('about');
            if ($aboutMeta === null) {
                $aboutMeta      = Meta::create();
                $aboutMeta->key = 'about';
            }

            if (!isset($aboutMeta->value['text'])) {
                $aboutMeta->value['text'] = '';
            }

            return new HtmlResponse($this->view->render('admin/about/about.html.twig', [
                'about' => $aboutMeta
            ]));
        });
    }
}