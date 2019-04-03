<?php
namespace Haskel\SchemaSerializer\EntityExtractor;

interface Extractor
{
    public function get($fieldName);
    public function exists($fieldName);
}