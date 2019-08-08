<?php


namespace Tests\Framework\Auth\Provider;


use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use App\User\User;
use App\User\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Auth\Provider\DatabaseAuthProvider;
use QuantFrame\Database\ActiveRecord\Record;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use QuantFrame\Database\ActiveRecord\Storage\StorageResult;

class DatabaseAuthProviderTest extends AsyncTestCase
{
    public function testGetByUsername()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        /** @var UserRepository|MockObject $repository */
        $repository = $this->createMock(UserRepository::class);
        $provider   = new DatabaseAuthProvider($repository);
        $storage->expects(static::once())->method('execute')->willReturn(StorageResult::create(null, [], null));
        $provider->getByUsername('username');
    }

    public function testGetById()
    {
        /** @var UserRepository|MockObject $repository */
        $repository = $this->createMock(UserRepository::class);
        $userId     = 1;
        $user       = User::create();
        $user->id   = $userId;
        $repository->expects(static::once())->method('find')->with($userId)->willReturn(new Success($user));
        $provider    = new DatabaseAuthProvider($repository);
        $foundedUser = yield $provider->getById($userId);
        static::assertSame($user, $foundedUser);
    }
}