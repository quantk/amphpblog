<?php


namespace QuantFrame\Database\ActiveRecord\Storage;


interface StorageInterface
{

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     */
    public function execute(string $sql, array $values);
}