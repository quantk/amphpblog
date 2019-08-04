<?php


namespace QuantFrame\Auth\Provider;


use Amp\Promise;
use App\User\User;

interface AuthProvider
{
    /**
     * @param string $username
     * @return Promise|User|null
     * @psalm-return Promise<User|null>
     */
    public function getByUsername(string $username): Promise;

    /**
     * @param int $id
     * @return Promise|User|null
     * @psalm-return Promise<User|null>
     */
    public function getById(int $id): Promise;
}