<?php

namespace Amorphine\DataTransferObject\Interfaces;

interface IPropertyCache
{
    /**
     * Get DTO property from cache (or put it if cache is empty)
     *
     * @param  string  $className
     * @param  callable  $callBack  - callback to remember values
     *
     * @return IProperty[]
     */
    public static function getClassProperties(string $className, callable $callBack): array;
}
