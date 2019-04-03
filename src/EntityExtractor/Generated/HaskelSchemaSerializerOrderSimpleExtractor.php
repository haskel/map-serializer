<?php
namespace Haskel\SchemaSerializer\EntityExtractor\Generated;

use ReflectionClass;
use ReflectionProperty;

final class HaskelSchemaSerializerOrderSimpleExtractor extends \Haskel\SchemaSerializer\EntityExtractor\BaseExtractor
{
    protected function extract()
    {
        return [
            "id" => $this->entity->getId(),
            "name" => $this->entity->getName(),
            "createdAt" => $this->entity->getCreatedAt(),
        ];
    }
}
