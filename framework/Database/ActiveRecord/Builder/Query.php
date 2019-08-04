<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


use Amp\Promise;
use Aura\SqlQuery\QueryFactory;
use QuantFrame\Database\ActiveRecord\Record;

/**
 * Class Query
 * @package QuantFrame\Database\ActiveRecord\Builder
 */
final class Query
{
    /**
     * @var Record
     */
    private $record;
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Query constructor.
     * @param Record $record
     */
    public function __construct(Record $record)
    {
        $this->record       = $record;
        $this->queryFactory = new QueryFactory('mysql');
    }

    /**
     * @param BaseQuery $query
     * @param array $options
     * @return \Amp\Promise
     */
    public function execute(BaseQuery $query, array $options = []): Promise
    {
        return $this->record->execute($query, $options);
    }

    /**
     * @param array $cols
     * @return Select
     */
    public function select(array $cols = []): Select
    {
        return new Select($this, $this->queryFactory->newSelect()->from($this->getTableName())->cols(empty($cols) ? ['*'] : $cols));
    }

    /**
     * @return string
     */
    private function getTableName(): string
    {
        return $this->record->getTableName();
    }

    /**
     * @return Update
     */
    public function update(): Update
    {
        return new Update($this, $this->queryFactory->newUpdate()->table($this->getTableName()));
    }

    /**
     * @return Insert
     */
    public function insert(): Insert
    {
        return new Insert($this, $this->queryFactory->newInsert()->into($this->getTableName()));
    }

    /**
     * @return Delete
     */
    public function delete(): Delete
    {
        return new Delete($this, $this->queryFactory->newDelete()->from($this->getTableName()));
    }
}