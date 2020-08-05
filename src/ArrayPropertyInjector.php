<?php

namespace Amorphine\DataTransferObject;

use Amorphine\DataTransferObject\Exceptions\DataTransferObjectError;
use Amorphine\DataTransferObject\Helpers\ValueCast;
use Amorphine\DataTransferObject\Interfaces\IDataTransferObject;
use Amorphine\DataTransferObject\Interfaces\IProperty;
use Amorphine\DataTransferObject\Interfaces\IPropertyInjector;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class PropertyInjector
 *
 * Sets up DTO properties from source
 *
 * @package Amorphine\DataTransferObject
 */
class ArrayPropertyInjector implements IPropertyInjector
{
    private static $instance;

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function inject(IDataTransferObject $target, $source)
    {
        $properties = $this->getDataTransferObjectProperties($target);

        foreach ($properties as $propertyName => $property) {
            $sourceField = $property->getSource();

            $sourceValueSet = isset($source[$sourceField]);

            if (!$sourceValueSet) {
                // source does not contain value but there's default one, so ve leave it unchanged
                if ($property->hasDefaultValue()) {
                    continue;
                }

                if (!$property->isNullable() && !$property->isDefault()) {
                    // no source value, not default and field is not nullable, throwing exception
                    throw DataTransferObjectError::uninitialized(
                        static::class,
                        $property->getSource()
                    );
                }
            }

            $value = $source[$sourceField] ?? $this->{$propertyName} ?? null;

            $value = ValueCast::castValueToPropertyType($property, $value);

            if (!$property->isValidType($value)) {
                throw DataTransferObjectError::invalidType(
                    get_class($target),
                    $propertyName,
                    $property->getTypes(),
                    $value
                );
            }

            $target->{$propertyName} = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public static function getInstance(): IPropertyInjector
    {
        if (!static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Extract an array of properties of data transfer object
     *
     * @param  IDataTransferObject  $object
     *
     * @return IProperty[]
     * @throws ReflectionException
     */
    private function getDataTransferObjectProperties(IDataTransferObject $object): array
    {
        $class = new ReflectionClass($object);

        // cache data transfer object properties
        return PropertyCache::getClassProperties($class->getName(), function () use ($class) {
            $result = [];

            $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($properties as $reflectionProperty) {

                if ($reflectionProperty->isStatic()) {
                    continue;
                }

                $property = new DocTypedProperty($reflectionProperty);

                $result[$property->getName()] = $property;
            }

            return $result;
        });
    }
}
