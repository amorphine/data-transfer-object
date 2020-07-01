<?php


namespace Amorphine\DataTransferObject;

use Amorphine\DataTransferObject\Helpers\ClassUseResolver;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class DocTypedProperty
 * Realization of Property class where types are being resolved through `@var` annotations
 *
 * @package Amorphine\DataTransferObject
 */
final class DocTypedProperty extends Property
{
    const VAR_DOCBLOCK_REGEX = '/@var ((?:(?:[\w?|\\\\<>])+(?:\[])?)+)/';

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

    const NORMALIZING_TYPE_MAP = [
        self::SCALAR_INT => 'integer',
        self::SCALAR_BOOL => 'boolean',
        self::SCALAR_FLOAT => 'float',
        self::SCALAR_DOUBLE => 'float',
    ];

    /**
     * Type declaration as string
     *
     * @var string|null
     */
    protected $typeDeclaration;

    public function __construct(ReflectionProperty $property)
    {
        parent::__construct($property);

        $docComment = $property->getDocComment();

        preg_match(
            self::VAR_DOCBLOCK_REGEX,
            $docComment,
            $varStrMatches
        );

        $typeDeclaration = $varStrMatches[1] ?? null;

        $this->types = $this->normalizeTypes(explode('|', $typeDeclaration), $property->getDeclaringClass());

        $this->typeDeclaration = implode('|', $this->types);

        $this->isNullable = $this->checkIsNullable($this->types);

        $this->isMixed = $this->checkIsMixed($this->types);

        $this->isMixedArray = $this->checkIsMixedArray($this->types);

        $this->arrayTypes = $this->resolveArrayTypes($this->types);
    }


    /**
     * Replace scalar declarations with full variants
     * Replace short class names with full class names
     *
     * @param  array  $types  - type declaration
     * @param  ReflectionClass  $rs - class where property declared
     * @return array
     * @see DocTypedProperty::NORMALIZING_TYPE_MAP - type map
     */
    private function normalizeTypes(array $types, ReflectionClass $rs): array
    {
        $types = array_filter(array_map(
            function (string $type) use ($rs) {
                $type = self::NORMALIZING_TYPE_MAP[$type] ?? $type;

                // return empty type, scalar type immediately
                if (!$type || in_array($type, self::SCALAR_TYPES, true)) {
                    return $type;
                }

                // replace short class name with full one
                $splitRegex = '/([\[|]|<|>|])/i';
                $type = preg_split($splitRegex, $type, -1, PREG_SPLIT_DELIM_CAPTURE);

                return implode(array_map(function ($type) use($rs, $splitRegex){
                    if (!$type) {
                        return $type;
                    }

                    // split symbols and php doc types should not be cast
                    if (preg_match($splitRegex, $type)) {
                        return $type;
                    }

                    return ClassUseResolver::resolveClassName($type, $rs);

                }, $type));
            },
            $types
        ));

        return $types;
    }

    /**
     * Define whether the property is nullable
     *
     * @param  string[]  $types
     * @return bool
     */
    private function checkIsNullable(array $types)
    {
        foreach ($types as $type) {
            if (in_array($type, ['mixed', 'null', '?'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check type declared `mixed`
     *
     * @param  string[]  $types
     * @return bool
     */
    private function checkIsMixed(array $types)
    {
        return !$types || in_array('mixed', $types);
    }

    /**
     * Check property contains some of ['array', 'iterable'] declarations
     *
     * @param  string[]  $types
     * @return bool
     */
    private function checkIsMixedArray(array $types)
    {
        foreach ($types as $type) {
            if (in_array($type, ['array', 'iterable', 'mixed[]', 'iterator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get typed array declaration
     *
     * @param  string[]  $types
     * @return array
     */
    private function resolveArrayTypes(array $types)
    {
        return array_filter(
            array_map(function ($type) {
                if (strpos($type, '[]') !== false) {
                    return str_replace('[]', '', $type);
                }

                if (strpos($type, 'iterable<') !== false) {
                    return str_replace(['iterable<', '>'], ['', ''], $type);
                }

                if (strpos($type, 'iterator<') !== false) {
                    return str_replace(['iterator<', '>'], ['', ''], $type);
                }

                return null;
            }, $types)
        );
    }
}
