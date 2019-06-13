<?php
namespace Haskel\MapSerializer\Formatter;

use Haskel\MapSerializer\Exception\FormatterException;
use ReflectionClass;

class EnumFormatter implements Formatter
{
    public function format($value, $schemaName)
    {
        if (!is_object($value)) {
            throw new FormatterException(sprintf('wrong value type'));
        }

        $reflectionClass = new ReflectionClass($value);
        return $reflectionClass->getConstants();
    }
}