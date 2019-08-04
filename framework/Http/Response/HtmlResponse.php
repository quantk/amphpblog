<?php


namespace QuantFrame\Http\Response;


class HtmlResponse extends Response
{
    /**
     * @var string
     */
    private $contentType = 'text/html';

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
}