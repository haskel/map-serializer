<?php

namespace Haskel\MapSerializer\Formatter;

use Haskel\MapSerializer\Exception\FormatterException;
use ReflectionClass;

class ObjectFormatter implements Formatter
{
    public function format($value, $schemaName)
    {
        if (!is_object($value)) {
            throw new FormatterException(sprintf('wrong value type'));
        }

        $reflectionClass = new ReflectionClass($value);


        $result = [];
        foreach ($reflectionClass->getProperties() as $prop) {
            $prop->setAccessible(true);
            $propValue = $prop->getValue($value);
            if (is_object($propValue)) {
                $propValue = $this->format($propValue, $schemaName);
            }
            if (is_array($propValue)) {
                foreach ($propValue as $key => $item) {
                    if (is_object($item)) {
                        $propValue[$key] = $this->format($item, $schemaName);
                    }
                }
            }
            $result[$prop->getName()] = $propValue;
        }

        return $result;
    }
}