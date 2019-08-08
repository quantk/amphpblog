<?php


namespace Tests\Framework\Database\ActiveRecord\Stub;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class TestRecord extends Record
{
    protected static $table = 'table';

    /**
     * @var int
     * @Field(type="int", id=true, autoincrement=false)
     */
    public $id;

    /**
     * @var int
     * @Field(type="varchar")
     */
    public $name;
}