<?php

namespace Amorphine\DataTransferObject;

use Amorphine\DataTransferObject\Interfaces\IPropertyCache;

/**
 * Cache for classes properties
 *
 * Class PropertyCache
 *
 * @package Amorphine\DataTransferObject
 */
class PropertyCache implements IPropertyCache
{
    private static $cache = [];

    /**
     * @inheritDoc
     *
     * @param  string  $className
     * @param  callable  $callBack
     * @return array
     */
    public static function getClassProperties(string $className, callable $callBack): array
    {
        $result = self::$cache[$className] ?? [];

        if (!$result) {
            self::$cache[$className] = $callBack();
        }

        return self::$cache[$className];
    }
}
