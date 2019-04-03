<?php
namespace Haskel\SchemaSerializer\EntityExtractor\Generated;

use ReflectionClass;
use ReflectionProperty;

final class HaskelSchemaSerializerOrderTestExtractor extends \Haskel\SchemaSerializer\EntityExtractor\BaseExtractor
{
    protected function extract()
    {
        return [
            "id" => $this->entity->getId(),
            "name" => $this->entity->getName(),
            "createdAt" => $this->entity->getCreatedAt(),
            "owner" => $this->getPropertyValue('owner'),
            "user" => $this->getPropertyValue('user'),
            "externalId" => $this->entity->externalId,
        ];
    }
}
