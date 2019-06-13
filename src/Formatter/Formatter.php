<?php

namespace Haskel\MapSerializer\Formatter;

interface Formatter
{
    public function format($value, $schemaName);
}