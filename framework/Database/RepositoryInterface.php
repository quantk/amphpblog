<?php


namespace QuantFrame\Database;


use Amp\Promise;

interface RepositoryInterface
{
    /**
     * @param int $id
     * @return Promise
     */
    public function find(int $id): Promise;
}