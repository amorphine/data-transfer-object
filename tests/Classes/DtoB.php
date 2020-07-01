<?php


namespace Amorphine\DataTransferObject\Tests\Classes;


class DtoB extends \Amorphine\DataTransferObject\DataTransferObject
{
    /**
     * @var integer
     */
    public static $staticField;
    /**
     * @var integer
     */
    public $integerField;
    /**
     * @var null|DtoC
     */
    public $dtoField;
}
