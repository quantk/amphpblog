<?php


namespace QuantFrame\Database\ActiveRecord\Annotation;


/**
 * Class Field
 * @package QuantFrame\Database\ActiveRecord\Annotation
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Field
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $id = false;

    /**
     * @var bool
     */
    public $autoincrement = false;
}