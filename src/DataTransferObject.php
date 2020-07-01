<?php

namespace Amorphine\DataTransferObject;

use Amorphine\DataTransferObject\Interfaces\IDataTransferObject;

/**
 * Class DataTransferObject
 * @package Amorphine\DataTransferObject
 */
abstract class DataTransferObject implements IDataTransferObject
{
    public function __construct($source)
    {
        $propertyInjector = ArrayPropertyInjector::getInstance();

        $propertyInjector->inject($this, $source);
    }
}
