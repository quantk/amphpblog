<?php


namespace Tests\Framework\Database\ActiveRecord;


use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Database\ActiveRecord\Builder\Select;
use QuantFrame\Database\ActiveRecord\Record;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use QuantFrame\Database\ActiveRecord\Storage\StorageResult;
use Tests\Framework\Database\ActiveRecord\Stub\TestRecord;

class RecordTest extends AsyncTestCase
{
    public function testWhere()
    {
        $select = TestRecord::where('1 = 1');
        static::assertInstanceOf(Select::class, $select);
    }

    public function testOrderBy()
    {
        $select = TestRecord::orderBy(['id DESC']);
        static::assertInstanceOf(Select::class, $select);
    }

    public function testGroupBy()
    {
        $select = TestRecord::groupBy(['id DESC']);
        static::assertInstanceOf(Select::class, $select);
    }

    public function testRaw()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $result = StorageResult::create(1, [], 1);
        $storage->method('execute')->willReturn(new Success($result));

        $storageResult = yield TestRecord::raw('SELECT * FROM users', []);
        static::assertSame($result, $storageResult);
    }

//    public function testFind()
//    {
//        /** @var MockObject|StorageInterface $storage */
//        $storage = $this->createMock(StorageInterface::class);
//        Record::initialize($storage);
//
//        $storageResult = StorageResult::create(null, [
//            [
//                'id' => 1,
//                'name' => 'name'
//            ]
//        ], null);
//        $storage->method('execute')->willReturn(new Success($storageResult));
//
//        /** @var TestRecord $testRecord */
//        $testRecord = yield TestRecord::find(1);
//        static::assertSame($testRecord->id, 1);
//        static::assertSame($testRecord->name, 'name');
//    }
}