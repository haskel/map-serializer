<?php
namespace Haskel\MapSerializer\EntityExtractor;

use ArrayAccess;
use Haskel\MapSerializer\Exception\PropertyNotFoundException;
use Haskel\MapSerializer\Exception\SerializerException;
use ReflectionClass;

class FieldExtractor implements Extractor
{
    /**
     * @var mixed
     */
    private $entity;

    /**
     * @var array
     */
    private $objectVars = [];

    /**
     * @var array
     */
    private $cached = [];

    /**
     * @var ReflectionClass
     */
    private $objectReflection;

    public function __construct($entity)
    {
        $this->entity = $entity;
        if (is_object($entity)) {
            $this->objectVars       = get_object_vars($entity);
            $this->objectReflection = new ReflectionClass($entity);
        }
    }

    /**
     * @param mixed $fieldName
     *
     * @return bool
     */
    public function exists($fieldName)
    {
        try {
            $this->get($fieldName);
            return true;
        } catch (PropertyNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param mixed $fieldName
     *
     * @return mixed
     */
    public function get($fieldName)
    {
        if (array_key_exists($fieldName, $this->cached)) {
            return $this->cached[$fieldName];
        }
        $this->cached[$fieldName] = $this->extractValue($fieldName);

        return $this->cached[$fieldName];
    }

    /**
     * @param $fieldName
     *
     * @return mixed
     */
    private function extractValue($fieldName)
    {
        if (is_array($this->entity) && array_key_exists($fieldName, $this->entity)) {
            return $this->entity[$fieldName];
        }

        if (is_object($this->entity)) {
            if (!property_exists($this->entity, $fieldName)) {
                $message = sprintf("property %s not found in class '%s'", $fieldName, get_class($this->entity));
                throw new PropertyNotFoundException($message, $this->entity);
            }

            if (array_key_exists($fieldName, $this->objectVars)) {
                return $this->objectVars[$fieldName];
            }

            $getter = "get" . ucfirst($fieldName);
            if (method_exists($this->entity, $getter)) {
                return $this->entity->{$getter}();
            }

            $reflectionProperty = $this->objectReflection->getProperty($fieldName);
            $reflectionProperty->setAccessible(true);
            return $reflectionProperty->getValue($this->entity);
        }

        $message = sprintf("property %s not found", $fieldName);
        throw new PropertyNotFoundException($message, $this->entity);
    }
}