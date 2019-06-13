<?php
namespace Haskel\MapSerializer\EntityExtractor;

use ReflectionClass;

abstract class BaseExtractor implements Extractor
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @param $fieldName
     *
     * @return bool
     */
    public function exists($fieldName)
    {
        if (count($this->fields) === 0) {
            $this->fields = $this->extract();
        }

        return array_key_exists($fieldName, $this->fields);
    }

    /**
     * @param $fieldName
     *
     * @return mixed
     */
    public function get($fieldName)
    {
        if (count($this->fields) === 0) {
            $this->fields = $this->extract();
        }

        return $this->fields[$fieldName];
    }

    protected function getPropertyValue($fieldName)
    {
        $objectReflection = new ReflectionClass($this->entity);
        $reflectionProperty = $objectReflection->getProperty($fieldName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($this->entity);
    }

    /**
     * @return array
     */
    abstract protected function extract();
}