<?php


namespace Amorphine\DataTransferObject\Tests\Classes;


use Amorphine\DataTransferObject\DataTransferObject;

class TestPlainArrayDataTransferObject extends DataTransferObject
{
    public static $staticField;
    /**
     * @var int|null
     */
    public $generalField;
    /**
     * @var string|null
     * @source COMMENTARIY_DLYA_CLIENTA
     */
    public $fieldWithSource;
    /**
     * @var string[]|null
     */
    public $CapitalizedField;
}
