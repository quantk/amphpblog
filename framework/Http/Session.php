<?php


namespace QuantFrame\Http;


use Amp\Promise;
use function Amp\call;

class Session
{
    /**
     * @var \Amp\Http\Server\Session\Session
     */
    private $session;

    /**
     * Session constructor.
     * @param \Amp\Http\Server\Session\Session $session
     */
    public function __construct(\Amp\Http\Server\Session\Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->session->getId();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->session->get($key);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return \Amp\Promise
     */
    public function set(string $key, $data): Promise
    {
        return call(function () use ($key, $data) {
            yield $this->session->open();
            $this->session->set($key, $data);
            yield $this->session->save();
        });
    }

    /**
     * @return \Amp\Promise
     */
    public function destroy(): Promise
    {
        return call(function () {
            yield $this->session->open();
            yield $this->session->destroy();
        });
    }


}