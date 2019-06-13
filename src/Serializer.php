<?php
namespace Haskel\MapSerializer;

use Haskel\MapSerializer\EntityExtractor\Extractor;
use Haskel\MapSerializer\EntityExtractor\FieldExtractor;
use Haskel\MapSerializer\Exception\SerializerException;
use Haskel\MapSerializer\Formatter\Formatter;
use Haskel\MapSerializer\Formatter\ObjectFormatter;
use Haskel\MapSerializer\Formatter\ScalarFormatter;
use Haskel\MapSerializer\Schema\SpecialField;

class Serializer
{
    private $schemas = [];

    /**
     * @var Formatter[]
     */
    private $formatters = [];

    private $extractors = [];

    private $defaultSchemaName = 'default';

    private $showNullable = true;

    private $ignoreUnknownFields = true;

    private $extractorsDir = '';

    public function __construct()
    {
        $this->initDefault();
    }

    private function initDefault()
    {
        $this->addFormatter(new ScalarFormatter());
        $scalarTypes = ['int', 'float', 'string', 'boolean'];
        foreach ($scalarTypes as $scalarType) {
            $this->addSchema('scalar', $scalarType, [SpecialField::FORMATTER => ScalarFormatter::class]);
        }

        $this->addFormatter(new ObjectFormatter());
        $this->addSchema('object', $this->defaultSchemaName, [SpecialField::FORMATTER => ObjectFormatter::class]);
    }

    /**
     * @param string $className
     * @param string $name
     * @param array $schema
     */
    public function addSchema($className, $name, array $schema)
    {
        $this->schemas[$className][$name] = $schema;
    }

    public function addFormatter(Formatter $formatter)
    {
        $this->formatters[get_class($formatter)] = $formatter;
    }

    public function addExtractor($className, $name, $extractorClass)
    {
        $this->extractors[$className][$name] = $extractorClass;
    }

    public function setExtractorsDir($extractorsDir)
    {
        $this->extractorsDir = $extractorsDir;
    }

    public function setContext()
    {

    }

    /**
     * @param              $entity
     * @param null         $schemaName
     * @param Context|null $context
     *
     * @return array|string|null
     */
    public function serialize($entity, $schemaName = 'default', Context $context = null)
    {
        if (is_iterable($entity)) {
            $result = [];
            foreach ($entity as $key => $entityItem) {
                $result[$key] = $this->serialize($entityItem, $schemaName);
            }

            return $result;
        }

        if (is_object($entity) || is_scalar($entity)) {
            $schema = $this->getSchema($entity, $schemaName);

            return $this->transformBySchema($entity, $schema, $schemaName);
        }

        return $this->showNullable ? null : [];
    }

    /**
     * @param mixed $entity
     * @param string $name
     *
     * @return array
     */
    private function getSchema($entity, $name = null)
    {
        $name = $name ?: $this->defaultSchemaName;

        if (is_object($entity)) {
            $type = get_class($entity);
            $schema = $this->schemas[$type][$name] ?? $this->schemas['object'][$name] ?? null;
            if (!$schema) {
                throw new SerializerException(sprintf("schema '%s' for '%s' is undefined", $name, $type));
            }
            return $schema;
        }

        // if scalar
        if ($name === $this->defaultSchemaName) {
            $name = gettype($name);
        }

        return $this->schemas['scalar'][$name];
    }

    /**
     * @param $entity
     *
     * @return Extractor
     */
    private function getEntityExtractor($entity, $schemaName = null)
    {
        $className = get_class($entity);
        if (isset($this->extractors[$className][$schemaName])) {
            $extractorClass = $this->extractors[$className][$schemaName];
            $this->loadExtractor($extractorClass);
            return new $extractorClass($entity);
        }

        return new FieldExtractor($entity);
    }

    private function loadExtractor($extractorClass)
    {
        $name = substr_replace($extractorClass, "", 0, strrpos($extractorClass, "\\") + 1);
        $file = $this->extractorsDir . "/" . $name . ".php";
        if (file_exists($file)) {
            @include_once $file;
        }
    }

    /**
     * @param $name
     *
     * @return Formatter
     */
    private function getFormatter($name)
    {
        if (!isset($this->formatters[$name])) {
            throw new SerializerException(sprintf("formatter was not found: %s", $name));
        }

        return $this->formatters[$name];
    }

    /**
     * @param $entity
     * @param $schema
     *
     * @return array
     */
    public function transformBySchema($entity, $schema, $schemaName = null)
    {
        if (isset($schema[SpecialField::FORMATTER])) {
            $formatter = $this->getFormatter($schema[SpecialField::FORMATTER]);
            return $formatter->format($entity, $schemaName);
        }

        $struct = [];
        $extractor = $this->getEntityExtractor($entity, $schemaName);
        foreach ($schema as $fieldName => $fieldSchema) {
            if (!$extractor->exists($fieldName) && $this->ignoreUnknownFields) {
                continue;
            }
            $value = $extractor->get($fieldName);
            if ($value === null && !$this->showNullable) {
                continue;
            }
            $struct[$fieldName] = $this->serialize($value, $fieldSchema);
        }

        return $struct;
    }
}