<?php
namespace Haskel\SchemaSerializer\Formatter;

use DateTime;
use Haskel\SchemaSerializer\Exception\FormatterException;

class DatetimeFormatter implements Formatter
{
    public function format($value, $schemaName)
    {
        if (!$value instanceof DateTime) {
            throw new FormatterException(sprintf('wrong value type'));
        }

        switch ($schemaName) {
            case 'default':
            case 'datetime':
            default:
                return $value->format("Y-m-d H:i:s");

            case 'date':
                return $value->format('Y-m-d');

            case 'time':
                return $value->format('H:i:s');
        }
    }
}