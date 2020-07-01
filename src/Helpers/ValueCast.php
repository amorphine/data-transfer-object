<?php


namespace Amorphine\DataTransferObject\Helpers;


use Amorphine\DataTransferObject\DataTransferObject;
use Amorphine\DataTransferObject\Interfaces\IProperty;

/**
 * Class ValueCast
 *
 * @package Amorphine\DataTransferObject\Helpers
 * @author Spatie bvba <info@spatie.be>
 */
class ValueCast
{
    const SCALAR_INT = 'int';
    const SCALAR_INTEGER = 'integer';
    const SCALAR_BOOL = 'bool';
    const SCALAR_BOOLEAN = 'boolean';
    const SCALAR_FLOAT = 'float';
    const SCALAR_DOUBLE = 'double';

    const SCALAR_TYPES = [
        self::SCALAR_INT,
        self::SCALAR_INTEGER,
        self::SCALAR_BOOL,
        self::SCALAR_BOOLEAN,
        self::SCALAR_FLOAT,
        self::SCALAR_DOUBLE,
    ];

    /**
     * Cast $value to declared property type if needed
     *
     * @param  IProperty  $property
     *
     * @param  mixed  $value
     * @return array|mixed
     */
    public static function castValueToPropertyType(IProperty $property, $value) {
        // if property is mixed, return value as it is
        if($property->isMixed()) {
            return $value;
        }

        // nullable property with nullable value
        if($value === null) {
            return null;
        }

        $propertyTypes = $property->getTypes();

        $valueType = gettype($value);

        // if value type is allowed, pass as it is
        if (in_array($valueType, $propertyTypes)) {
            return $value;
        }

        // typed iterable properties values should be cast to DTO or collection of DTO, or be returned as they are
        if (Polyfills::isIterable($value)) {
            return self::shouldBeCastToCollection($value)
                ? self::castToDataTransferObjectCollection($value, $property->getArrayTypes())
                : self::castToDataTransferObject($value, $property->getTypes());
        } elseif (is_scalar($value)) {
            // cast scalar value to closest allowed scalar type
            foreach ($propertyTypes as $type) {
                if (!in_array($type, self::SCALAR_TYPES)) {
                    continue;
                }

                settype($value, $type);

                break;
            }
        }

        return $value;
    }

    /**
     * Cast an array to DTO instance if possible. Otherwise same $value is returned
     *
     * @param $value
     * @param  array|iterable  $types
     *
     * @return mixed
     */
    protected static function castToDataTransferObject($value, array $types)
    {
        $castTo = null;

        // cast to another DTO instance
        foreach ($types as $type) {
            if (!is_subclass_of($type, DataTransferObject::class)) {
                continue;
            }

            $castTo = $type;

            break;
        }

        // we have nothing to cast to
        if (!$castTo) {
            return $value;
        }

        return new $castTo($value);
    }

    /**
     * Cast multidimensional array to typed array is possible. Otherwise same array is being returned
     * If $arrayTyoes contain DTO class, collection of DTO will return
     *
     * @param $values
     * @param  array|iterable  $arrayTypes
     *
     * @return array
     */
    protected static function castToDataTransferObjectCollection($values, array $arrayTypes)
    {
        $castTo = null;

        foreach ($arrayTypes as $type) {
            if (!is_subclass_of($type, DataTransferObject::class)) {
                continue;
            }

            $castTo = $type;

            break;
        }

        if (!$castTo) {
            return $values;
        }

        $casts = [];

        foreach ($values as $value) {
            $casts[] = new $castTo($value);
        }

        return $casts;
    }

    /**
     * Check source should be cast to collection
     *
     * @param  array|iterable  $values
     * @return bool
     */
    protected static function shouldBeCastToCollection($values): bool
    {
        if (empty($values)) {
            return false;
        }

        foreach ($values as $key => $value) {
            // associative array should not to be casted to collection
            if (is_string($key)) {
                return false;
            }

            // plain arrays should not to be casted to collection
            if (!is_array($value)) {
                return false;
            }
        }

        return true;
    }
}
