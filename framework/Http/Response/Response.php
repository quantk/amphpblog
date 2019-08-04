<?php


namespace QuantFrame\Http\Response;


abstract class Response implements ResponseInterface
{
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var array
     */
    protected $headers;

    /**
     * Response constructor.
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($data, int $statusCode = 200, array $headers = [])
    {
        $this->data                    = $data;
        $this->statusCode              = $statusCode;
        $this->headers                 = $headers;
        $this->headers['content-type'] = $this->getContentType();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}