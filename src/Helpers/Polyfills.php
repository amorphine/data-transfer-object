<?php


namespace Amorphine\DataTransferObject\Helpers;

use Traversable;

/**
 * Class Polyfills
 *
 * Helper class shipping methods to smooth different PHP versions incompatibility
 * Declared methods should be removed as soon as possible along dropping old PHP version support
 *
 * @package Amorphine\DataTransferObject\Helpers
 */
class Polyfills
{
    /**
     * Perform `is_iterable` php 7.1+ action
     *
     * @param $value
     *
     * @return bool
     */
    public static function isIterable($value): bool
    {
        if (function_exists('is_iterable')) {
            return is_iterable($value);
        } else {
            return is_array($value) || (is_object($value) && ($value instanceof Traversable));
        }
    }
}
