<?php


namespace QuantFrame\View;


use Psr\Log\LoggerInterface;
use Twig\Environment;

class View
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * View constructor.
     * @param Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(Environment $twig, LoggerInterface $logger)
    {
        $this->twig   = $twig;
        $this->logger = $logger;
    }

    /**
     * @param string $name
     * @param array $context
     * @return string
     * @throws \Throwable
     */
    public function render(string $name, array $context = []): string
    {
        try {
            return $this->twig->render($name, $context);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw $e;
        }
    }
}