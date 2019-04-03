<?php

namespace Haskel\SchemaSerializer\Formatter;

interface Formatter
{
    public function format($value, $schemaName);
}