<?php

namespace Amorphine\DataTransferObject;

use Amorphine\DataTransferObject\Helpers\Polyfills;
use Amorphine\DataTransferObject\Interfaces\IProperty;
use ReflectionProperty;

/**
 * Represents property of data transfer object
 *
 * Class Property
 *
 * @package Amorphine\DataTransferObject
 */
abstract class Property implements IProperty
{
    const SOURCE_DOCBLOCK_REGEX = '/@source ((?:(?:[\w?|\\\\<>])+(?:\[])?)+)/';

    /**
     * @var string
     */
    protected $name;

    /**
     * Property types
     *
     * @var string[]
     */
    protected $types = [];

    /**
     * Source field name where the field will take the value
     *
     * @var string|null
     */
    protected $source;

    /**
     * Property is nullable
     *
     * @var bool
     */
    protected $isNullable = false;

    /**
     * TRUE if the property was declared at compile-time, or FALSE if it was created at run-time.
     *
     * @var bool
     */
    protected $isDefault = true;

    /**
     * Property is mixed
     *
     * @var bool
     */
    protected $isMixed = false;

    /**
     * Type contains 'array'/'iterable' declaration
     *
     * @var bool
     */
    protected $isMixedArray = false;

    /**
     * Declared array types
     *
     * @var string[]
     */
    protected $arrayTypes = [];

    public function __construct(ReflectionProperty $property)
    {
        $docComment = $property->getDocComment();

        preg_match(
            self::SOURCE_DOCBLOCK_REGEX,
            $docComment,
            $sourceStrMatches
        );

        $this->name = $property->getName();

        $this->source = $sourceStrMatches[1] ?? $this->name;

        $this->isDefault = $property->isDefault();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @inheritDoc
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @inheritDoc
     */
    public function isMixed(): bool
    {
        return $this->isMixed;
    }

    /**
     * @inheritDoc
     */
    public function isMixedArray(): bool
    {
        return $this->isMixedArray;
    }

    /**
     * @inheritDoc
     */
    public function getArrayTypes(): array
    {
        return $this->arrayTypes;
    }

    /**
     * @inheritDoc
     */
    public function isValidType($value): bool
    {
        if (!$this->types) {
            return true;
        }

        if ($this->isMixed) {
            return true;
        }

        if (Polyfills::isIterable($value) && $this->isMixedArray) {
            return true;
        }

        if ($this->isNullable && $value === null) {
            return true;
        }

        if (Polyfills::isIterable($value)) {
            foreach ($this->arrayTypes as $type) {
                $isValid = $this->assertValidArrayTypes($type, $value);

                if ($isValid) {
                    return true;
                }
            }
        }

        foreach ($this->types as $type) {
            $isValidType = $this->assertValidType($type, $value);

            if ($isValidType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check property type is compatible with value type
     *
     * @param  string  $type
     * @param  mixed  $value
     *
     * @return bool
     */
    protected function assertValidType(string $type, $value): bool
    {
        return $value instanceof $type || gettype($value) === $type;
    }

    /**
     * Check type is compatible with collection values type
     *
     * @param  string  $type
     * @param $collection
     *
     * @return bool
     */
    protected function assertValidArrayTypes(string $type, $collection): bool
    {
        foreach ($collection as $value) {
            if (!$this->assertValidType($type, $value)) {
                return false;
            }
        }

        return true;
    }
}
