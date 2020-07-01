<?php

namespace Amorphine\DataTransferObject\Tests;

use Amorphine\DataTransferObject\DataTransferObject;
use Amorphine\DataTransferObject\Exceptions\DataTransferObjectError;
use Amorphine\DataTransferObject\Helpers\Str;
use Amorphine\DataTransferObject\Tests\Classes\DtoA;
use Amorphine\DataTransferObject\Tests\Classes\DtoB;
use ArrayIterator;
use Iterator;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTransferObjectTest
 * @package Amorphine\DataTransferObject\Tests
 */
class DataTransferObjectTest extends TestCase
{

    function testResolveAnnotatedSourceSupport()
    {
        $v = 999;
        $dto = new class(['fieldWitchStrangeName' => $v]) extends DataTransferObject {
            /** @source fieldWitchStrangeName */
            public $fieldWithNormalName;
        };

        $this->assertSame($v, $dto->fieldWithNormalName);
    }

    function testResolveNotAnnotatedSource()
    {
        $v = 999;

        $dto = new class(['field' => $v]) extends DataTransferObject {
            public $field;
        };

        $this->assertSame($v, $dto->field);
    }

    function testUnionTypes()
    {
        $v = 999;

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /** @var string|integer */
            public $fieldName;
        };

        $this->assertSame($v, $dto->fieldName);

        $v = 1;

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /** @var string|boolean|integer */
            public $fieldName;
        };

        $this->assertSame($v, $dto->fieldName);
    }

    public function testMixedProperty()
    {
        $dto = new class(['fieldName' => 'string']) extends DataTransferObject {
            /** @var mixed|boolean */
            public $fieldName;
        };

        $this->assertSame('string', $dto->fieldName);

        $dto = new class(['fieldName' => 'string']) extends DataTransferObject {
            /** @var null|boolean|mixed| */
            public $fieldName;
        };

        $this->assertSame('string', $dto->fieldName);
    }

    function testNullablePropertyFromNotExistingSource()
    {
        $dto = new class(['existingField' => 999]) extends DataTransferObject {
            /**
             * @var null|integer
             * @source nonExistingSourceField
             */
            public $fieldName;
        };

        $this->assertSame(null, $dto->fieldName);
    }

    function testResolveFromNotExistingSource()
    {
        $this->expectException(DataTransferObjectError::class);

        $dto = new class(['existingField' => 999]) extends DataTransferObject {
            /**
             * @var integer
             * @source nonExistingSourceField
             */
            public $fieldName;
        };
    }

    function testArrayTypedProperty()
    {
        $v = [1, 2, 3];

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /** @var array */
            public $fieldName;
        };

        $this->assertSame($v, $dto->fieldName);

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /** @var integer[] */
            public $fieldName;
        };

        $this->assertSame($v, $dto->fieldName);

        $this->expectException(DataTransferObjectError::class);

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /**
             * @var string[]
             */
            public $fieldName;
        };
    }

    function testNotDtoCastTypeFromArray() {
        $this->expectException(DataTransferObjectError::class);

        $dto = new class(['fieldName' => [['field' => 1], ['field' => 2]]]) extends DataTransferObject {
            /** @var ArrayIterator */
            public $fieldName;
        };
    }

    function testCastEmptyArray() {
        $dto = new class(['fieldName' => []]) extends DataTransferObject {
            /** @var integer[] */
            public $fieldName;
        };

        $this->assertEquals([], $dto->fieldName);
    }

    function testAssociativeArrayTypedProperty()
    {
        $v = [
            'field1' => 1,
            'field2' => 2,
        ];

        $dto = new class(['fieldName' => $v]) extends DataTransferObject {
            /** @var array */
            public $fieldName;
        };

        $this->assertSame($v, $dto->fieldName);
    }

    function testNestedDto() {
        $dto = new DtoA([
            'bField' => [
                'integerField' => 123,
            ],
            'bArrayField' => [
                [
                    'integerField' => '999'
                ],
                [
                    'integerField' => '888',
                    'dtoField' => [
                        'booleanField' => true,
                    ]
                ]
            ]
        ]);

        $this->assertNull($dto->bArrayField[0]->dtoField);
        $this->assertTrue($dto->bArrayField[1]->dtoField->booleanField);


        $dto = new class([
            'bField' => [
                'integerField' => '999'
            ],
        ]) extends DataTransferObject {
            /**
             * @var DtoB
             */
            public $bField;
        };

        $this->assertSame($dto->bField->integerField, 999);

        $dto = new class([
            'bFieldArr' => [
                ['integerField' => '999'],
                ['integerField' => '888']
            ],
        ]) extends DataTransferObject {
            /**
             * @var DtoB[]
             */
            public $bFieldArr;
        };

        $this->assertSame($dto->bFieldArr[0]->integerField, 999);
        $this->assertSame($dto->bFieldArr[1]->integerField, 888);


    }

    public function testIterableSupport() {
        $v = [1, 2, 3];

        $dto = new class(['fieldName' => new ArrayIterator($v)]) extends DataTransferObject {
            /** @var iterable */
            public $fieldName;
        };

        $this->assertEquals(new ArrayIterator($v), $dto->fieldName);

        $dto = new class(['fieldName' => new ArrayIterator($v)]) extends DataTransferObject {
            /** @var iterable<integer> */
            public $fieldName;
        };

        $this->assertEquals(new ArrayIterator($v), $dto->fieldName);

        $dto = new class(['fieldName' => new ArrayIterator($v)]) extends DataTransferObject {
            /** @var iterator<integer> */
            public $fieldName;
        };

        $this->assertEquals(new ArrayIterator($v), $dto->fieldName);

        $dto = new class([
            'bFieldIterable' => new ArrayIterator([
                ['integerField' => 777],
                ['integerField' => 666],
                ['integerField' => '555'],
            ])
        ]) extends DataTransferObject {
            /**
             * @var iterable<string>|iterable<\Amorphine\DataTransferObject\Tests\Classes\DtoB>
             */
            public $bFieldIterable;
        };
        $this->assertIsArray($dto->bFieldIterable);
        $this->assertCount(3, $dto->bFieldIterable);
        $this->assertEquals(777, $dto->bFieldIterable[0]->integerField);
        $this->assertEquals(666, $dto->bFieldIterable[1]->integerField);
        $this->assertSame(555, $dto->bFieldIterable[2]->integerField);
    }

    public function testStaticFieldsAreNotFilled() {

        $dto = new class(['staticField' => true]) extends DataTransferObject {
            public static $staticField;
        };

        $this->assertNull($dto::$staticField);
    }

    public function testUninitializedError() {

        $this->expectErrorMessageMatches('/Invalid type: expected `class@anonymous.*::integerField` to be of type `integer`, instead got value `null`, which is NULL/');

        $dto = new class([]) extends DataTransferObject {
            /** @var integer */
            public $integerField = 0;
        };
    }

    public function testNotScalarAndNotDtoType() {
        $this->expectException(DataTransferObjectError::class);

        $dto = new class(['field' => 1 ]) extends DataTransferObject {
            /** @var Str */
            public $field;
        };
    }

    public function constructionProvider(): array
    {
        return [
            'array' => [
                [
                    'null' => null,
                    'integer' => 123,
                    'integerZero' => 0,
                    'integerNegative' => -123,
                    'true' => true,
                    'false' => false,
                    'obj' => new \stdClass(),
                    'double' => 0.32425,
                    'negativeDouble' => -0.32425,
                    'string' => 'comment',
                    'stringTrue' => 'true',
                    'stringFalse' => 'false',
                    'stringZero' => '0',
                    'stringInteger' => '123',
                    'stringNegativeInteger' => '-123',
                    'stringDouble' => '0.123',
                    'stringNegativeDouble' => '0.123',
                    'array' => [
                        1, 'string', null, true
                    ],
                    'associativeArray' => [
                        'integer' => 1,
                        'string' => 'string',
                        'null' => null
                    ],
                ],
            ],
        ];
    }


}
