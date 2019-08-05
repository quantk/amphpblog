<?php

use App\Http\Controller\Auth\LoginController;
use App\Http\Controller\IndexController;
use App\Http\Controller\ProjectsController;
use FastRoute\RouteCollector;
use QuantFrame\Http\Handler;

$dispatcher = FastRoute\simpleDispatcher(static function (FastRoute\RouteCollector $r) {

    $r->get('/', Handler::create(IndexController::class, 'index', []));

    $r->addGroup('/auth', static function (RouteCollector $r) {

        $r->addRoute(['GET', 'POST'], '/login', Handler::create(LoginController::class, 'login', []));
        $r->addRoute(['GET', 'POST'], '/logout', Handler::create(LoginController::class, 'logout', []));

    });

    $r->get('/about', Handler::create(IndexController::class, 'about', []));

    $r->addGroup('/projects', static function (RouteCollector $r) {

        $r->get('', Handler::create(ProjectsController::class, 'index', []));
        $r->get('/{projectId:\d+}', Handler::create(ProjectsController::class, 'detail', []));

    });

    $r->addGroup('/admin', static function (RouteCollector $r) {

        $r->get('', Handler::create(App\Http\Controller\Admin\AdminController::class, 'index', ['secure' => true]));
        $r->addGroup('/projects', static function (RouteCollector $r) {

            $r->get('', Handler::create(App\Http\Controller\Admin\ProjectsController::class, 'page', ['secure' => true]));
            $r->addRoute(['GET', 'POST'], '/add', Handler::create(App\Http\Controller\Admin\ProjectsController::class, 'add', ['secure' => true]));
            $r->addRoute(['GET', 'POST'], '/edit/{projectId:\d+}', Handler::create(App\Http\Controller\Admin\ProjectsController::class, 'edit', ['secure' => true]));
            $r->get('/{projectId:\d+}', Handler::create(App\Http\Controller\Admin\ProjectsController::class, 'detail', ['secure' => true]));

        });
        $r->get('/notes', Handler::create(App\Http\Controller\Admin\NotesController::class, 'page', ['secure' => true]));
        $r->addGroup('/about', function (RouteCollector $r) {
//            $r->get('', Handler::create(App\Http\Controller\Admin\AboutController::class, 'page', ['secure' => true]));
            $r->addRoute(['GET', 'POST'], '/edit', Handler::create(App\Http\Controller\Admin\AboutController::class, 'edit', ['secure' => true]));
        });

    });

});

return $dispatcher;