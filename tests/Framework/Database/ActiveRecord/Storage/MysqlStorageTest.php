<?php


namespace Tests\Framework\Database\ActiveRecord\Storage;


use Amp\Mysql\ResultSet;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Database\ActiveRecord\Storage\MysqlStorage;
use QuantFrame\Database\ActiveRecord\Storage\StorageResult;

class MysqlStorageTest extends AsyncTestCase
{
    public function testExecuteResultSet()
    {
        /** @var \Amp\Sql\Pool::class|MockObject $pool */
        $pool = $this->createMock(\Amp\Sql\Pool::class);

        $storage  = new MysqlStorage($pool);
        $fakeSql  = 'SELECT * FROM users';
        $bindings = [];
        /** @var ResultSet|MockObject $resultSet */
        $resultSet = $this->createMock(ResultSet::class);
        $pool->expects(static::once())->method('execute')->with($fakeSql, $bindings)->willReturn(new Success($resultSet));
        $resultSet->method('advance')->willReturnOnConsecutiveCalls(new Success(true), new Success(false));
        $row = [
            'id' => 1
        ];
        $resultSet->expects(static::once())->method('getCurrent')->willReturn($row);

        /** @var StorageResult $storageResult */
        $storageResult = yield $storage->execute($fakeSql, $bindings);
        static::assertSame([$row], $storageResult->rows);
    }

    public function testCommandResult()
    {
        /** @var \Amp\Sql\Pool::class|MockObject $pool */
        $pool = $this->createMock(\Amp\Sql\Pool::class);

        $storage          = new MysqlStorage($pool);
        $fakeSql          = 'INSERT INTO users(id, name) VALUES(1,2)';
        $bindings         = [];
        $lastInsertId     = 1;
        $affectedRowCount = 1;

        $commandResult = new \Amp\Mysql\CommandResult($affectedRowCount, $lastInsertId);
        $pool->expects(static::once())->method('execute')->with($fakeSql, $bindings)->willReturn(new Success($commandResult));

        /** @var StorageResult $storageResult */
        $storageResult = yield $storage->execute($fakeSql, $bindings);
        static::assertSame($lastInsertId, $storageResult->lastInsertId);
        static::assertSame($affectedRowCount, $storageResult->affectedRowsCount);
    }
}