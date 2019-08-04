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
}