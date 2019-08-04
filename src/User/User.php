<?php


namespace App\User;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class User extends Record
{
    protected static $table = 'users';
    /**
     * @var int|null
     * @Field(type="int")
     */
    public $id;
    /**
     * @var string
     * @Field(type="varchar")
     */
    public $username;
    /**
     * @var string
     * @Field(type="varchar")
     */
    public $password;
}