<?php


namespace QuantFrame\Auth\Provider;


use Amp\Promise;
use App\User\User;
use App\User\UserRepository;

class DatabaseAuthProvider implements AuthProvider
{
    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * DatabaseAuthProvider constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $username
     * @return Promise|User|null
     * @psalm-return Promise<User|null>
     */
    public function getByUsername(string $username): Promise
    {
        return User::builder()->select()->where('username = :username')->bindValue('username', $username)->first();
    }

    /**
     * @param int $id
     * @return Promise|User|null
     * @psalm-return Promise<User|null>
     */
    public function getById(int $id): Promise
    {
        return $this->userRepository->find($id);
    }
}