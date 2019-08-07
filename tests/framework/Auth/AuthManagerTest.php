<?php


namespace Tests\Framework\Auth;


use Amp\Failure;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use App\User\User;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Auth\AuthManager;
use QuantFrame\Auth\Credentials;
use QuantFrame\Auth\Provider\AuthProvider;
use QuantFrame\Auth\Token\UserToken;
use QuantFrame\Http\Session;

class AuthManagerTest extends AsyncTestCase
{
    public function testInitializeWithoutToken()
    {
        /** @var MockObject|Session $session */
        $session = $this->createMock(Session::class);
        /** @var MockObject|AuthProvider $provider */
        $provider = $this->createMock(AuthProvider::class);

        $authManager = new AuthManager($provider);
        $authManager->setSession($session);

        $session->method('get')->with(AuthManager::SESSION_KEY)->willReturn(null);

        $session->expects(static::once())->method('set')->with(AuthManager::SESSION_KEY, null);

        yield $authManager->initialize();
    }

    public function testInitializeWithTokenAndWrongSessid()
    {
        /** @var MockObject|Session $session */
        $session          = $this->createMock(Session::class);
        $anotherSessionId = 'dfjkghsejhgh5j435jhf';
        $session->method('getId')->willReturn($anotherSessionId);
        /** @var MockObject|AuthProvider $provider */
        $provider = $this->createMock(AuthProvider::class);

        $authManager = new AuthManager($provider);
        $authManager->setSession($session);


        $sessid = 'sdgjqj43h5hdfgsdhgfsdkj52';
        $userId = 1;
        $session->method('get')->with(AuthManager::SESSION_KEY)->willReturn(serialize(new UserToken($userId, $sessid)));

        $session->expects(static::once())->method('set')->with(AuthManager::SESSION_KEY, null);

        yield $authManager->initialize();
    }

    public function testInitializeButUserNotFound()
    {
        /** @var MockObject|Session $session */
        $session = $this->createMock(Session::class);
        $sessid  = 'sdgjqj43h5hdfgsdhgfsdkj52';
        $session->method('getId')->willReturn($sessid);
        /** @var AuthProvider $provider */
        $provider = new class implements AuthProvider
        {
            /**
             * @param string $username
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getByUsername(string $username): Promise
            {
                return new Failure(new \RuntimeException());
            }

            /**
             * @param int $id
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getById(int $id): Promise
            {
                return new Success(null);
            }
        };

        $authManager = new AuthManager($provider);
        $authManager->setSession($session);
        $session->expects(static::once())->method('set')->with(AuthManager::SESSION_KEY, null);

        $userId = 1;
        $session->method('get')->with(AuthManager::SESSION_KEY)->willReturn(serialize(new UserToken($userId, $sessid)));

        $user = yield $authManager->initialize();
        static::assertNull($user);
        static::assertNull($authManager->getToken());
    }

    public function testInitialize()
    {
        /** @var MockObject|Session $session */
        $session = $this->createMock(Session::class);
        $sessid  = 'sdgjqj43h5hdfgsdhgfsdkj52';
        $session->method('getId')->willReturn($sessid);
        /** @var AuthProvider $provider */
        $provider = new class implements AuthProvider
        {
            /**
             * @param string $username
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getByUsername(string $username): Promise
            {
                return new Failure(new \RuntimeException());
            }

            /**
             * @param int $id
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getById(int $id): Promise
            {
                $user     = User::create();
                $user->id = $id;
                return new Success($user);
            }
        };

        $authManager = new AuthManager($provider);
        $authManager->setSession($session);
        $session->expects(static::never())->method('set')->with(AuthManager::SESSION_KEY, null);

        $userId = 1;
        $session->method('get')->with(AuthManager::SESSION_KEY)->willReturn(serialize(new UserToken($userId, $sessid)));

        $user = yield $authManager->initialize();
        static::assertNotNull($user);
        static::assertNotNull($authManager->getToken());

        static::assertSame($user->id, (yield $authManager->getUser())->id);
    }

    public function testAuthenticate()
    {
        /** @var MockObject|Session $session */
        $session = $this->createMock(Session::class);
        $sessid  = 'sdgjqj43h5hdfgsdhgfsdkj52';
        $session->method('getId')->willReturn($sessid);

        $expectedUsername = 'user';
        $credentials      = Credentials::create($expectedUsername, 'password');
        /** @var AuthProvider $provider */
        $provider = new class implements AuthProvider
        {
            /**
             * @param string $username
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getByUsername(string $username): Promise
            {
                if ($username === 'user') {
                    $user           = User::create();
                    $user->id       = 1;
                    $user->username = 'user';
                    $user->password = password_hash('password', PASSWORD_DEFAULT);
                    return new Success($user);
                }

                return new Failure(new \RuntimeException());
            }

            /**
             * @param int $id
             * @return Promise|User|null
             * @psalm-return Promise<User|null>
             */
            public function getById(int $id): Promise
            {
                return new Failure(new \RuntimeException());
            }
        };

        $authManager = new AuthManager($provider);
        $authManager->setSession($session);
        $token = $authManager->authenticate($credentials);
        static::assertNotNull($token);
    }
}