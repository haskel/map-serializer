<?php
namespace Haskel\MapSerializer\Exception;

use Exception;

class PropertyNotFoundException extends Exception
{
    private $entity;

    public function __construct($message = "", $entity = null)
    {
        parent::__construct($message);
        $this->entity = $entity;
    }
}