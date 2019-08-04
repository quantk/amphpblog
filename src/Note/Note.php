<?php


namespace App\Note;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class Note extends Record
{
    protected static $table = 'notes';

    /**
     * @var int
     * @Field(type="integer")
     */
    public $id;
    /**
     * @var string
     * @Field(type="varchar")
     */
    public $title;
    /**
     * @var string
     * @Field(type="text")
     */
    public $text;
}