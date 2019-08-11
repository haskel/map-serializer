<?php

namespace Haskel\MapSerializer;

class Type
{
    const INTEGER = 'int';
    const FLOAT   = 'float';
    const STRING  = 'string';
    const BOOLEAN = 'boolean';
    const SCALAR  = 'scalar';
    const OBJECT  = 'object';

    public static $scalar = [
        self::INTEGER,
        self::FLOAT,
        self::STRING,
        self::BOOLEAN,
    ];
}