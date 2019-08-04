<?php


namespace App\Http\Controller;


use Amp\Promise;
use App\Project\Project;
use App\Project\ProjectRepository;
use QuantFrame\Http\Controller;
use QuantFrame\Http\Response\HtmlResponse;
use QuantFrame\Http\Response\NotFoundResponse;
use QuantFrame\View\View;
use function Amp\call;

class ProjectsController extends Controller
{
    /**
     * @var View
     */
    private $view;
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(View $view, ProjectRepository $projectRepository)
    {
        $this->view              = $view;
        $this->projectRepository = $projectRepository;
    }

    /**
     * @param int $projectId
     * @return Promise
     */
    public function detail(int $projectId): Promise
    {
        return call(function () use ($projectId) {
            /** @var Project|null $project */
            $project = yield Project::find($projectId);

            if ($project === null) {
                return new NotFoundResponse();
            }

            return new HtmlResponse($this->view->render('projects_detail.html.twig', [
                'project' => $project
            ]));
        });
    }

    /**
     * @return Promise
     */
    public function index(): Promise
    {
        return call(function () {
            /** @var int $page */
            $page     = $this->request->query('page') ?? 1;
            $page     = (int)$page;
            /** @var Project[] $projects */
            $projects = yield Project::orderBy(['id DESC'])->page($page)->get();
            /** @var Project|null $lastProject */
            $lastProject      = $projects[count($projects) - 1] ?? null;
            $projectAfterPage = null;
            if ($lastProject) {
                /** @var Project $projectAfterPage */
                $projectAfterPage = yield Project::where('id < :id')
                    ->bindValue('id', $lastProject->id)
                    ->first();
            }

            $hasNextPage = $projectAfterPage !== null;

            return new HtmlResponse($this->view->render('projects.html.twig', [
                'projects' => $projects,
                'nextPage' => $hasNextPage ? '/projects?page=' . ($page + 1) : null,
                'prevPage' => $page > 1 ? '/projects?page=' . ($page - 1) : null
            ]));
        });
    }
}