<?php


namespace App\Project;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class Project extends Record
{
    protected static $table = 'projects';
    /**
     * @var int|null
     * @Field(type="int")
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