<?php


namespace QuantFrame\Auth;


use Amp\Promise;
use App\User\User;
use QuantFrame\Auth\Exception\PasswordDoesntMatch;
use QuantFrame\Auth\Provider\AuthProvider;
use QuantFrame\Auth\Token\TokenInterface;
use QuantFrame\Auth\Token\UserToken;
use QuantFrame\Http\Session;
use function Amp\call;

class AuthManager
{
    public const SESSION_KEY = 'user';

    /**
     * @var AuthProvider
     */
    private $provider;

    /**
     * @var TokenInterface|null
     */
    private $token;
    /**
     * @var Session
     */
    private $session;

    /**
     * AuthManager constructor.
     * @param AuthProvider $provider
     */
    public function __construct(
        AuthProvider $provider
    )
    {
        $this->provider = $provider;
    }

    /**
     * @param Session $session
     */
    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    /**
     * @return TokenInterface|null
     */
    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $token = $this->parseToken();
        return $token !== null;
    }

    /**
     * @return null|TokenInterface
     */
    private function parseToken(): ?TokenInterface
    {
        /** @var string $user */
        $user = $this->session->get(self::SESSION_KEY);
        /** @noinspection UnserializeExploitsInspection */
        /** @var TokenInterface|false $token */
        $token = unserialize($user);

        return $token ?: null;
    }

    /**
     * @return Promise<User|null>
     */
    public function initialize(): Promise
    {
        return call(function () {
            $token = $this->parseToken();

            if ($token === null) {
                yield $this->logout();
                return null;
            }

            if ($token->getSessid() !== $this->session->getId()) {
                yield $this->logout();
                return null;
            }

            if ($token->getSessid() === $this->session->getId()) {
                /** @var User|null $user */
                $user = yield $this->getUserByToken($token);
                if ($user === null) {
                    yield $this->logout();
                    return null;
                }
                $this->token = $token;
                return $user;
            }

            return null;
        });
    }

    /**
     * @return Promise<bool>
     */
    public function logout(): Promise
    {
        return call(function () {
            $this->token = null;
            yield $this->session->set(self::SESSION_KEY, null);
            return true;
        });
    }

    /**
     * @param TokenInterface $token
     * @return Promise<User|null>
     */
    private function getUserByToken(TokenInterface $token)
    {
        return call(function () use ($token) {
            if ($token->getSessid() === $this->session->getId()) {
                return $this->provider->getById($token->getId());
            }
            return null;
        });
    }

    /**
     * @return Promise
     * @psalm-return Promise<User|null>
     */
    public function getUser(): Promise
    {
        return call(function () {
            return $this->token ? $this->getUserByToken($this->token) : null;
        });
    }

    /**
     * @param Credentials $credentials
     * @return Promise
     */
    public function authenticate(Credentials $credentials): Promise
    {
        return call(function () use ($credentials) {
            $session = $this->session;
            if ($this->token) {
                return $this->token;
            }

            $provider = $this->provider;
            /** @var User|null $user */
            $user     = yield $provider->getByUsername($credentials->username);
            if (!$user || $user->id === null) {
                throw new \RuntimeException('User not found');
            }

            $isVerified = password_verify($credentials->password, $user->password);
            if (!$isVerified) {
                throw new PasswordDoesntMatch();
            }

            $sessionId = $session->getId();
            if ($sessionId === null) {
                throw new \RuntimeException('Invalid session');
            }

            $token       = new UserToken($user->id, $sessionId);
            $this->token = $token;

            yield $session->set(self::SESSION_KEY, serialize($token));

            return $token;
        });
    }

}