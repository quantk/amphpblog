<?php


namespace App\Http\Controller\Admin;


use Amp\Promise;
use App\Project\Project;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\Http\Response\NotFoundResponse;
use QuantFrame\Http\Response\RedirectResponse;
use QuantFrame\Http\Response\Response;
use QuantFrame\View\View;
use function Amp\call;
use function QuantFrame\truncate;

class ProjectsController extends Controller
{
    /**
     * @var View
     */
    private $view;

    /**
     * ProjectsController constructor.
     * @param View $view
     */
    public function __construct(
        View $view
    )
    {
        $this->view = $view;
    }

    /**
     * @return Promise<Response>
     */
    public function page(): Promise
    {
        return call(function () {
            return new HtmlResponse($this->view->render('admin/projects/projects.html.twig', ['projects' => yield Project::builder()->select()->get()]));
        });
    }

    /**
     * @param int $projectId
     * @return Promise
     */
    public function edit(int $projectId): Promise
    {
        return call(function () use ($projectId) {
            /** @var Project|null $project */
            $project = yield Project::find($projectId);
            if ($project === null) {
                return new NotFoundResponse();
            }

            if ($this->request->method === 'POST') {
                $errors = [];

                $form        = $this->request->form;
                $title       = $form->getValue('title');
                $text        = (string)$form->getValue('text');
                $previewText = truncate(strip_tags($text), 400);
                if (empty($title) || empty($text)) {
                    $errors[] = 'Title or text not found';
                    return new HtmlResponse($this->view->render('admin/projects/project_add.html.twig', [
                        'errors' => $errors
                    ]));
                }

                $project->title       = $title;
                $project->text        = $text;
                $project->previewText = $previewText;
                yield $project->save();

                /** @var int $projectId */
                $projectId = $project->id;

                return new RedirectResponse('/admin/projects/' . $projectId);
            }

            return new HtmlResponse($this->view->render('admin/projects/project_add.html.twig', [
                'project' => $project
            ]));
        });
    }

    /**
     * @return Promise<Response>
     */
    public function add(): Promise
    {
        return call(function () {
            if ($this->request->method === 'POST') {
                $errors = [];

                $form  = $this->request->form;
                $title = $form->getValue('title');
                $text  = $form->getValue('text');
                if (empty($title) || empty($text)) {
                    $errors[] = 'Title or text not found';
                    return new HtmlResponse($this->view->render('admin/projects/project_add.html.twig', [
                        'errors' => $errors
                    ]));
                }

                $project              = Project::create();
                $project->title       = $title;
                $project->text        = $text;
                $project->previewText = truncate(strip_tags($text), 400);

                yield $project->save();
                /** @var int $projectId */
                $projectId = $project->id;

                return new RedirectResponse('/admin/projects/' . $projectId);
            }

            return new HtmlResponse($this->view->render('admin/projects/project_add.html.twig'));
        });
    }

    /**
     * @param int $projectId
     * @return \Amp\Promise<Response>
     */
    public function detail(int $projectId): Promise
    {
        return call(function () use ($projectId) {
            /** @var Project|null $project */
            $project = yield Project::find($projectId);
            if ($project === null) {
                return new NotFoundResponse();
            }
            return new HtmlResponse($this->view->render('admin/projects/project_detail.html.twig', ['project' => $project]));
        });
    }
}