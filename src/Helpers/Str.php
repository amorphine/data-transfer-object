<?php

namespace Amorphine\DataTransferObject\Helpers;

class Str
{
    public static function contains(string $string, $needles): bool
    {
        $needles = (array) $needles;

        foreach ($needles as $search) {
            if (strpos($string, $search) !== false) {
                return true;
            }
        }

        return false;
    }
}
