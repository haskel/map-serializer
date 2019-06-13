<?php
namespace Haskel\MapSerializer\EntityExtractor;

interface Extractor
{
    public function get($fieldName);
    public function exists($fieldName);
}