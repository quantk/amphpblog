<?php


namespace QuantFrame\Http\Response;


class JsonResponse extends Response
{
    /**
     * @var string
     */
    private $contentType = 'application/json';

    /**
     * JsonResponse constructor.
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($data, int $statusCode = 200, array $headers = [])
    {
        parent::__construct(json_encode($data), $statusCode, $headers);
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
}