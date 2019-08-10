<?php


namespace App\Project;


use QuantFrame\Database\ActiveRecord\Annotation\Field;
use QuantFrame\Database\ActiveRecord\Record;

class Project extends Record
{
    protected static $table = 'projects';
    /**
     * @var int|null
     * @Field(type="integer", id=true, autoincrement=true)
     */
    public $id;
    /**
     * @var string
     * @Field(type="varchar")
     */
    public $title;
    /**
     * @var string
     * @Field(type="varchar", name="preview_text")
     */
    public $previewText;
    /**
     * @var string
     * @Field(type="text")
     */
    public $text;
}