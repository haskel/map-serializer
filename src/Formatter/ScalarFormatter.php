<?php
namespace Haskel\MapSerializer\Formatter;

use Haskel\MapSerializer\Exception\FormatterException;

class ScalarFormatter implements Formatter
{
    public function format($value, $schemaName)
    {
        if (!is_scalar($value)) {
            throw new FormatterException(sprintf('wrong value type'));
        }

        $schemaName = $schemaName ?: gettype($value);
        switch ($schemaName) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'boolean':
                return (bool) $value;
            case 'string':
            default:
                return (string) $value;
        }
    }
}