<?php

namespace Haskel\SchemaSerializer\EntityExtractor;

use Haskel\SchemaSerializer\Exception\ExtractorGeneratorException;
use Haskel\SchemaSerializer\Exception\PropertyNotFoundException;
use Haskel\SchemaSerializer\Exception\SerializerException;
use Haskel\SchemaSerializer\Schema\SpecialField;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use ReflectionClass;

class ExtractorGenerator
{
    const USE_PUBLIC_PROPERTY = 1;
    const USE_GETTER          = 2;
    const USE_ARRAY_ACCESS    = 3;
    const USE_REFLECTION      = 4;

    private $namespace = '';

    public function __construct($namespace = '')
    {
        $this->namespace = $namespace;
    }

    /**
     * @param $entityClass
     * @param $schemaName
     * @param $schema
     *
     * @return GeneratedClass
     */
    public function generate($entityClass, $schemaName, $schema)
    {
        if (isset($schema[SpecialField::FORMATTER])) {
            throw new ExtractorGeneratorException('Do not need generate an extractor if entity has a formatter');
        }

        $extractorSchema = $this->getExtractorSchema($entityClass, $schema);

        $methodLines = [];
        foreach ($extractorSchema as $fieldName => $accessMethod) {
            $methodLines[] = sprintf('"%s" => %s,',
                                     $fieldName,
                                     $this->generateAccessMethod($fieldName, $accessMethod, '$this->entity'));
        }

        $methodLines = implode("\n    ", $methodLines);
        $body = "return [\n    {$methodLines}\n];";


        $namespace = new PhpNamespace($this->namespace);
        $namespace->addUse('ReflectionClass');
        $namespace->addUse('ReflectionProperty');
        $className = str_replace("\\", "", $entityClass) . ucfirst($schemaName) . "Extractor";
        $class = $namespace->addClass($className);

        $class->setFinal()
              ->setExtends(BaseExtractor::class)
              ->addMethod("extract")
              ->setVisibility('protected')
              ->setBody($body);

        $printer = new PsrPrinter();

        $result = new GeneratedClass();
        $result->namespace = $this->namespace;
        $result->className = $className;
        $result->code = "<?php\n" . $printer->printNamespace($namespace);

        return $result;
    }

    private function generateAccessMethod($fieldName, $accessMethod, $entityLink)
    {
        switch ($accessMethod) {
            case self::USE_PUBLIC_PROPERTY;
                return $entityLink . '->' . $fieldName;

            case self::USE_GETTER;
                return sprintf("%s->get%s()", $entityLink, ucfirst($fieldName));

            case self::USE_ARRAY_ACCESS;
                return $entityLink . "['{$fieldName}']";

            case self::USE_REFLECTION;
                return "\$this->getPropertyValue('{$fieldName}')";
        }
    }

    /**
     * @param $entityClass
     * @param $schema
     *
     * @return array
     */
    private function getExtractorSchema($entityClass, $schema)
    {
        $class = new ReflectionClass($entityClass);
        $classes = [$class];
        while ($parent = $class->getParentClass()) {
            $classes[] = $parent->getName();
            $class = $parent;
        }


        $extractorSchema = [];
        foreach ($schema as $fieldName => $fieldSchema) {
            foreach ($classes as $class) {
                $extractorSchema[$fieldName] = $this->extractAccessMethod($class, $fieldName);
                if ($extractorSchema[$fieldName] !== null) {
                    break;
                }
            }

            if ($extractorSchema[$fieldName] === null) {
                $message = sprintf("property %s not found in class '%s'", $fieldName, $entityClass);
                throw new PropertyNotFoundException($message);
            }
        }

        return $extractorSchema;
    }

    /**
     * @param ReflectionClass $objectReflection
     * @param                 $fieldName
     *
     * @return int|null
     */
    private function extractAccessMethod(ReflectionClass $objectReflection, $fieldName)
    {
        $hasProperty = $objectReflection->hasProperty($fieldName);
        // if has public property
        if ($hasProperty) {
            $reflectionProperty = $objectReflection->getProperty($fieldName);
            if ($reflectionProperty->isPublic()) {
                return self::USE_PUBLIC_PROPERTY;
            }
        }

        // if has getter
        $getter = "get" . ucfirst($fieldName);
        if ($objectReflection->hasMethod($getter)) {
            return self::USE_GETTER;
        }

        // if interface of ArrayAccess
        if ($objectReflection->isSubclassOf('\ArrayAccess')) {
            return self::USE_ARRAY_ACCESS;
        }

        // use reflection
        if ($hasProperty) {
            return self::USE_REFLECTION;
        }

        return null;
    }
}