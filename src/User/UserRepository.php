<?php


namespace App\User;


use Amp\Promise;
use QuantFrame\Database\Repository;

class UserRepository extends Repository
{
    /**
     * @param int $id
     * @return Promise
     */
    public function find(int $id): Promise
    {
        return User::find($id);
    }
}