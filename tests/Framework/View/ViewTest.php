<?php


namespace Tests\Framework\View;


use Amp\PHPUnit\AsyncTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use QuantFrame\View\View;
use Twig\Environment;

class ViewTest extends AsyncTestCase
{
    public function testRender()
    {
        /** @var Environment|MockObject $twig */
        $twig   = $this->createMock(Environment::class);
        $logger = new NullLogger();
        $view   = new View($twig, $logger);

        $vars         = ['var' => 'var'];
        $templateName = 'test';

        $resultHtml = '<h1>hello</h1>';
        $twig->expects(static::once())->method('render')->with($templateName, $vars)->willReturn($resultHtml);

        $html = $view->render($templateName, $vars);
        static::assertSame($resultHtml, $html);
    }

    public function testRenderWithError()
    {
        /** @var Environment|MockObject $twig */
        $twig             = $this->createMock(Environment::class);
        $exceptionMessage = 'message';
        $twig->method('render')->willThrowException(new \RuntimeException($exceptionMessage));
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('critical')->with($exceptionMessage);
        $view = new View($twig, $logger);

        $vars         = ['var' => 'var'];
        $templateName = 'test';

        $this->expectException(\RuntimeException::class);
        $view->render($templateName, $vars);
    }
}