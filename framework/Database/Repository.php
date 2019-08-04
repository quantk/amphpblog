<?php


namespace QuantFrame\Database;


use Amp\Promise;

abstract class Repository implements RepositoryInterface
{
    /**
     * @param int $id
     * @return Promise
     */
    abstract public function find(int $id): Promise;
}