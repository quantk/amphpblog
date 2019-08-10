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
     * @var bool
     */
    private $exists = false;

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
    public function getTableName(): string
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
    public static function orderBy(array $spec): Select
    {
        return static::builder()->select()->orderBy($spec);
    }

    /**
     * @param array $spec
     * @return Select
     */
    public static function groupBy(array $spec): Select
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
     *
     */
    public function setExist(): void
    {
        $this->exists = true;
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
                ->where("`{$primaryKey}` = :id")
                ->bindValue('id', $id)
                ->get();

            return count($rows) > 0 ? $rows[0] : null;
        });
    }

    /**
     * @param Record $record
     * @param array $row
     * @param bool $exists
     * @return Record
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function hydrate(Record $record, array $row, bool $exists = true): Record
    {
        $reader      = new AnnotationReader();
        $r           = new \ReflectionClass($record);
        $obj         = $record;
        $obj->exists = $exists;
        foreach ($r->getProperties() as $property) {
            /** @var Field|null $ann */
            $ann = $reader->getPropertyAnnotation($property, Field::class);
            if (!$ann) {
                continue;
            }
            $property->setAccessible(true);

            $type = $ann->type;

            switch (strtolower($type)) {
                case 'integer':
                case 'int':
                    $property->setValue($obj, (int)$row[$property->getName()]);
                    break;
                case 'varchar':
                case 'text':
                    $property->setValue($obj, (string)$row[$property->getName()]);
                    break;
                case 'json':
                    $property->setValue($obj, json_decode((string)$row[$property->getName()], true));
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
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->exists;
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
     * @return AnnotationReader
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private static function initializeReader(): AnnotationReader
    {
        return new AnnotationReader();
    }

    /**
     * @return array<array>
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function prepareValues(): array
    {
        $reader = self::initializeReader();
        $r      = new \ReflectionClass($this);

        $valMap = [
            'basic' => [],
            'json'  => []
        ];
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

            if ($ann->id === true && $ann->autoincrement === true && static::$primaryKey === $fieldName) {
                continue;
            }

            switch (strtolower($ann->type)) {
                case 'json':
                    $val                        = json_encode($property->getValue($this) ?? []);
                    $valMap['json'][$fieldName] = $val;
                    break;
                default:
                    /** @var string $val */
                    $val                         = $property->getValue($this);
                    $valMap['basic'][$fieldName] = $val;
            }

            $values[$fieldName] = $val;
        }

        return [$valMap, $values];
    }

    /**
     * @param array $values
     * @param array $valMap
     * @param BaseQuery|Insert|Update $query
     * @psalm-param Insert|Update $query
     */
    private function bindValues(array $values, array $valMap, BaseQuery $query): void
    {
        /**
         * @var string $column
         * @var string|int|bool|null $value
         */
        foreach ($values as $column => $value) {
            if (isset($valMap['json'][$column])) {
                $query->set($column, 'CONVERT(:' . $column . ' USING UTF8MB4)');
            } else {
                $query->set($column, ':' . $column);
            }

            $query->bindValue($column, $value);
        }
    }

    /**
     * @param array $values
     * @param array $valMap
     * @return Promise<bool>
     */
    private function performUpdate(array $values, array $valMap): Promise
    {
        return call(function () use ($values, $valMap) {
            $primaryKey      = static::$primaryKey;
            $primaryKeyValue = $this->getPrimaryKeyValue();

            $query = static::builder()->update()
                ->table(static::$table)
                ->where("`{$primaryKey}` = :id")
                ->bindValue('id', $primaryKeyValue);

            $this->bindValues($values, $valMap, $query);

            /** @var StorageResult $result */
            $result = yield self::$storage->execute($query->toSql(), $query->getValues());

            return (int)$result->affectedRowsCount > 0;
        });
    }

    /**
     * @param array $values
     * @param array $valMap
     * @return Promise<bool>
     */
    private function performInsert(array $values, array $valMap): Promise
    {
        return call(function () use ($values, $valMap) {
            $query = static::builder()->insert()
                ->into(static::$table);

            $this->bindValues($values, $valMap, $query);
            /** @var StorageResult $result */
            $result = yield self::$storage->execute($query->toSql(), $query->getValues());
            if ($result->lastInsertId === null) {
                throw new \RuntimeException('lastInsertId is null when insert new row');
            }

            $this->setPrimaryKey($result->lastInsertId);
            $this->exists = true;

            return true;
        });
    }

    /**
     * @return Promise<bool>
     */
    public function save(): \Amp\Promise
    {
        return call(function () {
            [$valMap, $values] = $this->prepareValues();

            if ($this->exists === true) {
                return yield $this->performUpdate($values, $valMap);
            }

            return yield $this->performInsert($values, $valMap);
        });
    }
}