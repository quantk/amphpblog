<?php


namespace QuantFrame\Database\ActiveRecord;


use Amp\Promise;
use Doctrine\Common\Annotations\AnnotationReader;
use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Builder\BaseQuery;
use QuantFrame\Database\ActiveRecord\Builder\Delete;
use QuantFrame\Database\ActiveRecord\Builder\Insert;
use QuantFrame\Database\ActiveRecord\Builder\Query;
use QuantFrame\Database\ActiveRecord\Builder\Select;
use QuantFrame\Database\ActiveRecord\Builder\Update;
use QuantFrame\Database\ActiveRecord\Storage\StorageInterface;
use QuantFrame\Database\ActiveRecord\Storage\StorageResult;
use function Amp\call;

class Record
{
    /**
     * @var string
     */
    protected static $table;
    /**
     * @var string
     */
    protected static $primaryKey = 'id';
    /**
     * @var StorageInterface
     */
    private static $storage;

    /**
     * @param StorageInterface $storage
     * @internal
     */
    public static function initialize(StorageInterface $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * @return string
     * @internal
     */
    public function getTableName()
    {
        return static::$table;
    }

    /**
     * @return Query
     */
    public static function builder(): Query
    {
        return new Query(static::create());
    }

    /**
     * @param string $cond
     * @return Select
     */
    public static function where(string $cond): Select
    {
        return static::builder()->select()->where($cond);
    }

    /**
     * @param array $spec
     * @return Select
     */
    public static function orderBy(array $spec)
    {
        return static::builder()->select()->orderBy($spec);
    }

    /**
     * @param array $spec
     * @return Select
     */
    public static function groupBy(array $spec)
    {
        return static::builder()->select()->groupBy($spec);
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return Promise<StorageResult>
     */
    public static function raw(string $sql, array $bindings)
    {
        return call(static function () use ($sql, $bindings) {
            /** @var StorageResult $result */
            $result = yield self::$storage->execute($sql, $bindings);

            return $result;
        });
    }

    /**
     * @param BaseQuery $baseQuery
     * @param array $options
     * @return Promise<array<static>|bool|int>
     * @internal
     */
    public function execute(BaseQuery $baseQuery, array $options = []): Promise
    {
        return call(function () use ($baseQuery, $options) {
            if ($baseQuery instanceof Select) {
                $sql    = $baseQuery->toSql();
                $values = $baseQuery->getValues();
                /** @var StorageResult $result */
                $result = yield self::$storage->execute($sql, $values);
                $rows   = $result->rows;

                $resultRows = [];
                /** @var array $row */
                foreach ($rows as $row) {
                    $resultRows[] = static::hydrate(static::create(), $row);
                }

                /** @var bool $first */
                $first = $options['first'] ?? false;

                if ($first) {
                    return $resultRows[0] ?? null;
                }

                return $resultRows;
            }

            if ($baseQuery instanceof Update) {
                $sql    = $baseQuery->toSql();
                $values = $baseQuery->getValues();
                /** @var StorageResult $result */
                $result       = yield self::$storage->execute($sql, $values);
                $affectedRows = $result->affectedRowsCount;

                return $affectedRows > 0;
            }

            if ($baseQuery instanceof Delete) {
                $sql    = $baseQuery->toSql();
                $values = $baseQuery->getValues();
                /** @var StorageResult $result */
                $result       = yield self::$storage->execute($sql, $values);
                $affectedRows = $result->affectedRowsCount;

                return $affectedRows > 0;
            }

            if ($baseQuery instanceof Insert) {
                $sql    = $baseQuery->toSql();
                $values = $baseQuery->getValues();
                /** @var StorageResult $result */
                $result = yield self::$storage->execute($sql, $values);

                return $result->lastInsertId;
            }

            throw new \RuntimeException('Unknown base query type');

        });
    }

    /**
     * @param int|string $id
     * @return \Amp\Promise<static>
     */
    public static function find($id)
    {
        return call(static function () use ($id) {
            $query = static::builder()->select();

            $primaryKey = static::$primaryKey;

            /**
             * @var array $rows
             */
            $rows = yield $query->limit(1)
                ->where("{$primaryKey} = :id")
                ->bindValue('id', $id)
                ->get();

            return count($rows) > 0 ? $rows[0] : null;
        });
    }

    /**
     * @param Record $record
     * @param array $row
     * @return Record
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function hydrate(Record $record, array $row): Record
    {
        $reader = new AnnotationReader();
        $r      = new \ReflectionClass($record);
        $obj    = $record;
        foreach ($r->getProperties() as $property) {
            /** @var Field|null $ann */
            $ann = $reader->getPropertyAnnotation($property, Field::class);
            if (!$ann) {
                continue;
            }
            $property->setAccessible(true);

            $type = $ann->type;

            switch ($type) {
                case 'integer':
                case 'int':
                    $property->setValue($obj, (int)$row[$property->getName()]);
                    break;
                case 'varchar':
                case 'text':
                    $property->setValue($obj, (string)$row[$property->getName()]);
                    break;
            }
        }

        return $obj;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return int|string|null
     * @throws \ReflectionException
     */
    private function getPrimaryKeyValue()
    {
        $prop = new \ReflectionProperty($this, static::$primaryKey);
        $prop->setAccessible(true);
        /** @var int|string|null $value */
        $value = $prop->getValue($this);
        return $value;
    }

    /**
     * @param int|string|null $value
     * @throws \ReflectionException
     */
    private function setPrimaryKey($value): void
    {
        $prop = new \ReflectionProperty($this, static::$primaryKey);
        $prop->setAccessible(true);
        $prop->setValue($this, $value);
    }

    /**
     * @return Promise
     */
    public function delete(): Promise
    {
        return call(function () {
            $primaryKey      = static::$primaryKey;
            $primaryKeyValue = $this->getPrimaryKeyValue();
            if ($primaryKeyValue === null) {
                throw new \RuntimeException("Can't delete record without primaryKey value from table {$this->getTableName()}");
            }
            return yield static::builder()
                ->delete()
                ->where("{$primaryKey} = :id")
                ->bindValue('id', $primaryKeyValue)
                ->execute();
        });
    }

    /**
     * @return Promise<bool>
     */
    public function save(): \Amp\Promise
    {
        return call(function () {
            $reader = new AnnotationReader();
            $r      = new \ReflectionClass($this);

            /**
             * @var array $values
             */
            $values = [];

            foreach ($r->getProperties() as $property) {
                /** @var Field|null $ann */
                $ann = $reader->getPropertyAnnotation($property, Field::class);
                if (!$ann) {
                    continue;
                }

                $property->setAccessible(true);

                /** @var string $fieldName */
                $fieldName = $ann->name ?? $property->getName();

                if (static::$primaryKey === $fieldName) {
                    continue;
                }
                /** @var string $val */
                $val                = $property->getValue($this);
                $values[$fieldName] = $val;
            }

            $mode = null;

            $primaryKeyValue = $this->getPrimaryKeyValue();
            if ($primaryKeyValue !== null) {
                $primaryKey = static::$primaryKey;
                $query      = static::builder()->update()
                    ->table(static::$table)
                    ->where("{$primaryKey} = :id")
                    ->bindValue('id', $primaryKeyValue);
                $mode       = 'update';
            } else {
                $query = static::builder()->insert()
                    ->into(static::$table);
                $mode  = 'insert';
            }

            /**
             * @var string $column
             * @var string|int|bool|null $value
             */
            foreach ($values as $column => $value) {
                $query->set($column, ':' . $column);
                $query->bindValue($column, $value);
            }

            /** @var StorageResult $result */
            $result = yield self::$storage->execute($query->toSql(), $query->getValues());

            if ($mode === 'insert') {
                if ($result->lastInsertId === null) {
                    throw new \RuntimeException('lastInsertId is null when insert new row');
                }

                $this->setPrimaryKey($result->lastInsertId);
            }

            return true;
        });
    }
}