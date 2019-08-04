<?php


namespace QuantFrame\Database\ActiveRecord\Storage;


use Amp\Mysql\CommandResult;
use Amp\Mysql\Pool;
use Amp\Mysql\ResultSet;
use Amp\Promise;
use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;
use function Amp\call;

class MysqlStorage implements StorageInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * MysqlStorage constructor.
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;

    }

    /**
     * @param string $sql
     * @param array $values
     * @return Promise<StorageResult>
     */
    public function execute(string $sql, array $values): Promise
    {
        return call(function () use ($sql, $values) {
            /** @var ResultSet|CommandResult|null $result */
            $result = yield $this->pool->execute($sql, $values);

            if ($result instanceof ResultSet) {
                $rows = [];
                while (yield $result->advance()) {
                    $row    = $result->getCurrent();
                    $rows[] = $row;
                }

                return StorageResult::create(null, $rows, null);
            }

            if ($result instanceof CommandResult) {
                return StorageResult::create($result->getLastInsertId(), [], $result->getAffectedRowCount());
            }

            throw new \RuntimeException('Invalid result');
        });

    }
}