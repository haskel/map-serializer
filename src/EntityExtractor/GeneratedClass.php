<?php

namespace Haskel\MapSerializer\EntityExtractor;

class GeneratedClass
{
    public $className;
    public $namespace;
    public $code;

    private const FILE_PERMS = 0755;

    public function saveFile($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, self::FILE_PERMS, true);
        }
        $filename = $directory . DIRECTORY_SEPARATOR . $this->className . ".php";
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