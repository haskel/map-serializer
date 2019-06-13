<?php
namespace Haskel\MapSerializer\EntityExtractor\Generated;

use ReflectionClass;
use ReflectionProperty;

final class HaskelSchemaSerializerOrderSimpleExtractor extends \Haskel\MapSerializer\EntityExtractor\BaseExtractor
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
