<?php


namespace QuantFrame\Http;


use QuantFrame\Http\Request\Request;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
        $this->session = $request->session;
    }
}