<?php


namespace App\Meta;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class Meta extends Record
{
    protected static $table      = 'meta';
    protected static $primaryKey = 'key';

    /**
     * @var string
     * @Field(type="varchar", id=true)
     */
    public $key;

    /**
     * @var array
     * @Field(type="JSON")
     */
    public $value;
}