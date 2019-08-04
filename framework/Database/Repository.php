<?php


namespace QuantFrame\Database;


use Amp\Promise;
use QuantFrame\Database\ActiveRecord\Builder\Select;
use function Amp\call;

abstract class Repository implements RepositoryInterface
{
    /**
     * @param int $id
     * @return Promise
     */
    abstract public function find(int $id): Promise;

    /**
     * @param Select $query
     * @param int $page
     * @return Promise
     */
    public function paginate(Select $query, int $page = 1): Promise
    {
        return call(function () use ($query, $page) {
            return yield $query->page($page)->get();
        });
    }
}