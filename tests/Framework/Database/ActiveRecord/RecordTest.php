<?php


namespace Tests\Framework\Database\ActiveRecord;


use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use PHPUnit\Framework\MockObject\MockObject;
use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Builder\Select;
use QuantFrame\Database\ActiveRecord\Record;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use QuantFrame\Database\ActiveRecord\Storage\StorageResult;
use Tests\Framework\Database\ActiveRecord\Stub\TestRecord;

class RecordTest extends AsyncTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // idk why its not initialized with doctrine annotation reader.
        $field = new Field();
    }


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

    public function testFind()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $storageResult = StorageResult::create(null, [
            [
                'id'   => 1,
                'name' => 'name'
            ]
        ], null);
        $storage->method('execute')->willReturn(new Success($storageResult));

        /** @var TestRecord $testRecord */
        $testRecord = yield TestRecord::find(1);
        static::assertSame($testRecord->id, 1);
        static::assertSame($testRecord->name, 'name');
    }

    public function testDelete()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $storageResult = StorageResult::create(null, [], 1);
        $storage->method('execute')->willReturn(new Success($storageResult));

        $testRecord = TestRecord::create();
        $testRecord->setExist();
        $testRecord->id   = 1;
        $testRecord->name = 'Name';

        $result = yield $testRecord->delete();
        static::assertTrue($result);
    }

    public function testDeleteWithoutPrimaryKeyValue()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $storageResult = StorageResult::create(null, [], 1);
        $storage->method('execute')->willReturn(new Success($storageResult));

        $testRecord = TestRecord::create();
        $testRecord->setExist();
        $testRecord->id   = null;
        $testRecord->name = 'Name';

        $this->expectException(\RuntimeException::class);
        yield $testRecord->delete();
    }

    public function testReturnFalseIfDeleteNotSuccesfull()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $storageResult = StorageResult::create(null, [], null);
        $storage->method('execute')->willReturn(new Success($storageResult));

        $testRecord = TestRecord::create();
        $testRecord->setExist();
        $testRecord->id   = 1;
        $testRecord->name = 'Name';

        $result = yield $testRecord->delete();
        static::assertFalse($result);
    }

    public function testSaveRecord()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $lastInsertId  = 1;
        $storageResult = StorageResult::create($lastInsertId, [], null);
        $storage->method('execute')->willReturn(new Success($storageResult));

        $record       = TestRecord::create();
        $record->name = '123';

        yield $record->save();
        static::assertSame($record->id, $lastInsertId);
        static::assertTrue($record->isExists());
    }

    public function testUpdateRecord()
    {
        /** @var MockObject|StorageInterface $storage */
        $storage = $this->createMock(StorageInterface::class);
        Record::initialize($storage);

        $storageResult = StorageResult::create(null, [], 1);
        $storage->method('execute')->willReturn(new Success($storageResult));

        $record       = TestRecord::create();
        $record->id   = 1;
        $record->name = '123';
        $record->setExist();

        $result = yield $record->save();
        static::assertSame($record->id, 1);
        static::assertTrue($record->isExists());
        static::assertTrue($result);
    }
}