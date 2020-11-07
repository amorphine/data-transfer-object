<?php

namespace Amorphine\DataTransferObject\Tests;

use Amorphine\DataTransferObject\DocTypedProperty;
use Amorphine\DataTransferObject\Interfaces\IProperty;
use Amorphine\DataTransferObject\Tests\Classes\DtoC;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DocTypedPropertyTest extends TestCase
{

    public function testIsDefault()
    {
        list($defaultProperty) = $this->getProperties(new class() {
            public $defaultProperty;
        });

        $this->assertTrue($defaultProperty->isDefault());
    }

    /**
     * Get property array from class
     *
     * @param $class
     * @return IProperty[]
     * @throws \ReflectionException
     */
    private function getProperties($class): array
    {
        $reflectionClass = new ReflectionClass($class);

        return array_map(function ($property) {
            return new DocTypedProperty($property);
        }, $reflectionClass->getProperties());
    }

    public function testGetTypes()
    {
        list($propWithTypes, $propWithoutTypes) = $this->getProperties(new class() {
            /** @var integer|null|mixed */
            public $propWithTypes;

            public $propWithoutTypes;
        });

        $this->assertCount(3, $propWithTypes->getTypes());
        $this->assertCount(0, $propWithoutTypes->getTypes());
    }

    public function testGetName()
    {
        list($prop) = $this->getProperties(new class() {
            public $prop;
        });

        $this->assertSame('prop', $prop->getName());
    }

    public function testGetSource()
    {
        list($prop, $propWithoutSource) = $this->getProperties(new class() {
            /** @source propSource */
            public $prop;

            public $propWithoutSource;
        });

        $this->assertSame('propSource', $prop->getSource());
        $this->assertSame($propWithoutSource->getSource(), $propWithoutSource->getName());
    }

    public function testIsNullable()
    {
        list($nullableProperty, $notNullableProperty) = $this->getProperties(new class() {
            /** @var null */
            public $nullableProperty;

            /** @var integer */
            public $notNullableProperty;
        });

        $this->assertTrue($nullableProperty->isNullable());
        $this->assertFalse($notNullableProperty->isNullable());
    }

    public function testIsMixedArray()
    {
        list($arrProp, $mixedArrProp, $typedArrProp, $noArrProp) = $this->getProperties(new class() {
            /** @var array */
            public $arrProp;

            /** @var mixed[] */
            public $mixedArrProp;

            /** @var integer[] */
            public $typedArrProp;

            public $noArrProp;
        });

        $this->assertTrue($arrProp->isMixedArray());
        $this->assertTrue($mixedArrProp->isMixedArray());
        $this->assertFalse($typedArrProp->isMixedArray());
        $this->assertFalse($noArrProp->isMixedArray());
    }

    public function testIsMixed()
    {
        list($noDocProp, $nullProp, $mixedArrProp, $mixedProp, $integerProp, $arrayProp) = $this->getProperties(new class() {
            public $noDocProp;

            /** @var null */
            public $nullProp;

            /** @var mixed[] */
            public $mixedArrProp;

            /** @var mixed */
            public $mixedProp;

            /** @var integer */
            public $integerProp;

            /** @var array */
            public $arrayProp;
        });

        $this->assertTrue($noDocProp->isMixed());
        $this->assertTrue($mixedProp->isMixed());
        $this->assertFalse($nullProp->isMixed());
        $this->assertFalse($mixedArrProp->isMixed());
        $this->assertFalse($integerProp->isMixed());
        $this->assertFalse($arrayProp->isMixed());
    }

    public function testIsValidType()
    {
        list($field) = $this->getProperties(new class() {
            public $field;
        });
        $this->assertTrue($field->isValidType(1));
        $this->assertTrue($field->isValidType('str'));
        $this->assertTrue($field->isValidType(false));
        $this->assertTrue($field->isValidType(true));
        $this->assertTrue($field->isValidType([]));
        $this->assertTrue($field->isValidType(new DtoC(['booleanField' => 1])));

        list($field) = $this->getProperties(new class() {
            /** @var null */
            public $field;
        });
        $this->assertTrue($field->isValidType(null));
        $this->assertFalse($field->isValidType([]));
        $this->assertFalse($field->isValidType(1));
        $this->assertFalse($field->isValidType('0'));

        list($field) = $this->getProperties(new class() {
            /** @var mixed */
            public $field;
        });
        $this->assertTrue($field->isValidType(1));
        $this->assertTrue($field->isValidType('str'));
        $this->assertTrue($field->isValidType(false));
        $this->assertTrue($field->isValidType(true));
        $this->assertTrue($field->isValidType([]));
        $this->assertTrue($field->isValidType(new DtoC(['booleanField' => 1])));

        list($field) = $this->getProperties(new class() {
            /** @var mixed|\Amorphine\DataTransferObject\Tests\Classes\DtoC[] */
            public $field;
        });
        $this->assertTrue($field->isValidType(1));
        $this->assertTrue($field->isValidType('str'));
        $this->assertTrue($field->isValidType(false));
        $this->assertTrue($field->isValidType(true));
        $this->assertTrue($field->isValidType([]));
        $this->assertTrue($field->isValidType(new DtoC(['booleanField' => 1])));

        list($field) = $this->getProperties(new class() {
            /** @var array */
            public $field;
        });
        $this->assertTrue($field->isValidType([]));
        $this->assertTrue($field->isValidType(new ArrayIterator([])));
        $this->assertFalse($field->isValidType(1));

        list($field) = $this->getProperties(new class() {
            /** @var \Amorphine\DataTransferObject\Tests\Classes\DtoC[] */
            public $field;
        });
        $this->assertTrue($field->isValidType([new DtoC(['booleanField' => 1]), new DtoC(['booleanField' => 1])]));
        $this->assertTrue($field->isValidType(new ArrayIterator([])));
        $this->assertTrue($field->isValidType([]));
        $this->assertFalse($field->isValidType(1));

        list($field) = $this->getProperties(new class() {
            /** @var iterable */
            public $field;
        });
        $this->assertTrue($field->isValidType([]));
        $this->assertTrue($field->isValidType(new ArrayIterator([])));
        $this->assertFalse($field->isValidType(1));

        list($field) = $this->getProperties(new class() {
            /** @var iterable<\Amorphine\DataTransferObject\Tests\Classes\DtoC> */
            public $field;
        });
        $this->assertTrue($field->isValidType([new DtoC(['booleanField' => 1]), new DtoC(['booleanField' => 1])]));
        $this->assertTrue($field->isValidType(new ArrayIterator([])));
        $this->assertTrue($field->isValidType([]));
        $this->assertFalse($field->isValidType(1));
    }

    public function testGetArrayTypes()
    {
        list($field) = $this->getProperties(new class() {
            public $field;
        });
        $this->assertEmpty($field->getArrayTypes());

        list($field) = $this->getProperties(new class() {
            /** @var array */
            public $field;
        });
        $this->assertEmpty($field->getArrayTypes());

        list($field) = $this->getProperties(new class() {
            /** @var iterable */
            public $field;
        });
        $this->assertEmpty($field->getArrayTypes());

        list($field) = $this->getProperties(new class() {
            /** @var integer[] */
            public $field;
        });
        $this->assertEquals(['integer'], $field->getArrayTypes());

        list($field) = $this->getProperties(new class() {
            /** @var DtoC[] */
            public $field;
        });
        $this->assertEquals(['Amorphine\DataTransferObject\Tests\Classes\DtoC'], $field->getArrayTypes());
    }

    public function testNativePropertiesAreIncluded() {
        list($prop) = $this->getProperties(new class() {
            /** @var string $prop */
            public ?int $prop;
        });

        $this->assertTrue($prop->isNullable());
        $this->assertTrue(in_array('integer', $prop->getTypes()));
        $this->assertTrue(in_array('string', $prop->getTypes()));
    }
}
