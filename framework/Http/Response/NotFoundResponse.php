<?php


namespace QuantFrame\Http\Response;


class NotFoundResponse extends Response
{
    /**
     * NotFoundResponse constructor.
     */
    public function __construct()
    {
        parent::__construct(null, 404);
    }


    public function getContentType(): string
    {
        return '';
    }
}