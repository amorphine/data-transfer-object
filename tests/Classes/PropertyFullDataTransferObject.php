<?php


namespace Amorphine\DataTransferObject\Tests\Classes;


class PropertyFullDataTransferObject
{

    public $docLessField;

    /**
     * @source source
     */
    public $docLessFieldWithSource;

    /**
     * @var integer
     */
    public $docIntegerField;

    /**
     * @var integer
     * @source anotherField
     */
    public $docIntegerFieldWithSource;

    /**
     * @var integer[]
     */
    public $docIntegerArrayField;

    /**
     * @var iterable<integer>
     */
    public $docIntegerIterableField;
}
