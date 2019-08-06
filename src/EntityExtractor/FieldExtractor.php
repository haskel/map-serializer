<?php
namespace Haskel\MapSerializer\EntityExtractor;

use ArrayAccess;
use Haskel\MapSerializer\Exception\PropertyNotFoundException;
use Haskel\MapSerializer\Exception\SerializerException;
use ReflectionClass;
use ReflectionProperty;

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
//            $this->objectVars       = get_object_vars($entity);
            $this->objectReflection = new ReflectionClass($entity);
            $this->objectVars = $this->getObjectProperties(get_class($entity), $entity);
        }
    }
    
    private function getObjectProperties($class, $object)
    {
        $vars = [];
        $reflection = new ReflectionClass($class);
        $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $vars[$prop->getName()] = $prop->getValue($object);
        }

        if ($parentClass = $reflection->getParentClass()) {
            $parentVars = $this->getObjectProperties($parentClass->getName(), $object);
            if (count($parentVars)) {
                $vars = array_merge($parentVars, $vars);
            }
        }

        return $vars;
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
            // непонятно зачем это проверять + нельзя так проверить родительсткие свойства у родителей
//            if (!property_exists($this->entity, $fieldName)) {
//                $message = sprintf("property %s not found in class '%s'", $fieldName, get_class($this->entity));
//                throw new PropertyNotFoundException($message, $this->entity);
//            }

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