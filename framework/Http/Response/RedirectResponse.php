<?php


namespace QuantFrame\Http\Response;


class RedirectResponse extends Response
{
    /**
     * @var string
     */
    public $targetUrl;

    /**
     * RedirectResponse constructor.
     * @param string $targetUrl
     * @param array $headers
     */
    public function __construct(string $targetUrl, array $headers = [])
    {
        $this->targetUrl = $targetUrl;
        parent::__construct(null);
        $this->headers = $headers;
    }

    public function getContentType(): string
    {
        return '';
    }
}