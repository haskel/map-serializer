<?php

namespace Haskel\SchemaSerializer\EntityExtractor;

class GeneratedClass
{
    public $className;
    public $namespace;
    public $code;

    public function saveFile($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = $directory . "/" . $this->className . ".php";
        file_put_contents($filename, $this->code);
    }

    /**
     * @return string
     */
    public function getFullClassName()
    {
        return $this->namespace . "\\" . $this->className;
    }
}