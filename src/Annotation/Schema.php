<?php
namespace Haskel\SchemaSerializer\Annotation;

/**
 * @Annotation
 */
class Schema
{
    /**
     * @var string
     */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}