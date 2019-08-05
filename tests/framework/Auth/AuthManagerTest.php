<?php


namespace Tests\Framework\Auth;


use Amp\PHPUnit\AsyncTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Auth\AuthManager;
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
}