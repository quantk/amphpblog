<?php


namespace QuantFrame\Http\Request;


use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\FormParser\Form;
use Psr\Http\Message\UriInterface;
use QuantFrame\Http\Session;
use function Amp\call;

class Request
{
    /**
     * @var Client
     */
    public $client;
    /**
     * @var Session
     */
    public $session;
    /**
     * @var string
     */
    public $method;
    /**
     * @var UriInterface
     */
    public $uri;
    /**
     * @var string
     */
    public $protocol;
    /**
     * @var Form
     */
    public $form;
    /**
     * @var array
     */
    private $uriParts;

    /**
     * Request constructor.
     * @param Client $client
     * @param string $method
     * @param UriInterface $uri
     * @param string $protocol
     * @param Form $form
     * @param Session $session
     * @param array $uriParts
     */
    public function __construct(
        Client $client,
        string $method,
        UriInterface $uri,
        string $protocol,
        Form $form,
        Session $session,
        array $uriParts = []
    )
    {
        $this->client   = $client;
        $this->method   = $method;
        $this->uri      = $uri;
        $this->protocol = $protocol;
        $this->form     = $form;
        $this->session  = $session;
        $this->uriParts = $uriParts;
    }

    /**
     * @param string $fileKey
     * @return \Amp\Http\Server\FormParser\File|null
     */
    public function file(string $fileKey)
    {
        return $this->form->getFile($fileKey);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function query(string $key)
    {
        return $this->uriParts[$key] ?? null;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key)
    {
        return $this->form->getValue($key);
    }

    /**
     * @param \Amp\Http\Server\Request $request
     * @psalm-suppress MixedReturnTypeCoercion
     * @return \Amp\Promise<Request>
     */
    public static function createFromAmpRequest(\Amp\Http\Server\Request $request)
    {
        return call(function () use ($request) {
            /** @var Form $form */
            $form = yield \Amp\Http\Server\FormParser\parseForm($request);
            parse_str($request->getUri()->getQuery(), $parts);
            /** @var \Amp\Http\Server\Session\Session $ampSession */
            $ampSession = $request->getAttribute(\Amp\Http\Server\Session\Session::class);
            return new static(
                $request->getClient(),
                $request->getMethod(),
                $request->getUri(),
                $request->getProtocolVersion(),
                $form,
                new Session($ampSession),
                $parts
            );
        });
    }
}