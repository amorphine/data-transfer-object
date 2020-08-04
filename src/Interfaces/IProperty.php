<?php

namespace Amorphine\DataTransferObject\Interfaces;


use ReflectionClass;
use ReflectionProperty;

/**
 * Class Property
 *
 * Represents data transfer object property
 *
 * @package Amorphine\DataTransferObject
 */
interface IProperty
{
    /**
     * Property name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Array of available types
     *
     * @return string[]
     */
    public function getTypes(): array;

    /**
     * Get source name
     *
     * @return string|null
     */
    public function getSource(): string;

    /**
     * Nullable field
     *
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Check the field has been declared at compile time
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Field marked as mixed
     *
     * @return bool
     */
    public function isMixed(): bool;

    /**
     * There are 'array' or 'iterable' types
     *
     * @return bool
     */
    public function isMixedArray(): bool;

    /**
     * Typed array types like 'string[]' etc.
     *
     * @return string[]
     */
    public function getArrayTypes(): array;

    /**
     * Check value can be assigned to the property
     *
     * @param  mixed  $value
     * @return bool
     */
    public function isValidType($value): bool;

    /**
     * Get original reflection property
     *
     * @return ReflectionProperty
     */
    public function getReflectionProperty(): ReflectionProperty;

    /**
     * Get declaring class
     *
     * @return ReflectionClass
     */
    public function getDeclaringClass(): ReflectionClass;

    /**
     * Check property has default value
     *
     * @return bool
     */
    public function hasDefaultValue(): bool;
}
