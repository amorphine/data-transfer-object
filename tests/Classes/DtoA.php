<?php


namespace Amorphine\DataTransferObject\Tests\Classes;


use Amorphine\DataTransferObject\DataTransferObject;

class DtoA extends DataTransferObject
{
    /**
     * @var DtoB
     */
    public $bField;

    /**
     * @var DtoB[]
     */
    public $bArrayField;
}
