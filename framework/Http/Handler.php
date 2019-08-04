<?php


namespace QuantFrame\Http;


class Handler
{
    /**
     * @var string
     */
    public $controller;
    /**
     * @var string
     */
    public $method;
    /**
     * @var array
     */
    public $options = [];

    /**
     * Handler constructor.
     * @param string $controller
     * @param string $method
     * @param array $options
     */
    public function __construct(
        string $controller,
        string $method,
        array $options = []
    )
    {
        $this->controller = $controller;
        $this->method     = $method;
        $this->options    = $options;
    }

    /**
     * @param string $controller
     * @param string $method
     * @param array $options
     * @return Handler
     */
    public static function create(string $controller, string $method, array $options = []): self
    {
        return new static($controller, $method, $options);
    }

}