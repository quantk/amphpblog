<?php


namespace QuantFrame\Database\ActiveRecord\Storage;


/**
 * Class StorageResult
 * @package QuantFrame\Database\ActiveRecord\Storage
 */
class StorageResult
{
    /**
     * @var null|int|string
     */
    public $lastInsertId = null;

    /**
     * @var null|int
     */
    public $affectedRowsCount = null;

    /**
     * @var array
     */
    public $rows = [];

    /**
     * StorageResult constructor.
     * @param int|string|null $lastInsertId
     * @param array $rows
     * @param int|null $affectedRowsCount
     */
    public function __construct($lastInsertId, array $rows, ?int $affectedRowsCount)
    {
        $this->lastInsertId      = $lastInsertId;
        $this->rows              = $rows;
        $this->affectedRowsCount = $affectedRowsCount;
    }

    /**
     * @param int|null $lastInsertId
     * @param array $rows
     * @param int|null $affectedRowsCount
     * @return StorageResult
     */
    public static function create($lastInsertId, array $rows, ?int $affectedRowsCount)
    {
        return new static($lastInsertId, $rows, $affectedRowsCount);
    }
}