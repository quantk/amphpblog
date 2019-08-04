<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


interface BaseQuery
{
    /**
     * @return string
     */
    public function toSql();

    /**
     * @return array
     */
    public function getValues();
}