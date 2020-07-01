<?php

namespace Amorphine\DataTransferObject\Helpers;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Class UseClassNameResolver
 * Util to extract full qualified class names from classes (through 'use' statement)
 * Original source has been taken from `nette/di` project
 *
 * @see https://github.com/nette/di/blob/ed1b90255688b08b87ae641f2bf1dfcba586ae5b/src/DI/PhpReflection.php
 * @author https://davidgrudl.com/
 *
 * @package Amorphine\DataTransferObject\Helpers
 */
class ClassUseResolver
{
    /** @var array */
    private static $cache = [];

    /**
     * Resolve full qualified class name by short name and class context where it's used
     *
     * @param  string $class - short class name
     * @param  ReflectionClass $rc  - class where short name is used
     * @return string  full name
     */
    public static function resolveClassName($class, ReflectionClass $rc)
    {
        $lower = strtolower($class);
        if (empty($class)) {
            throw new InvalidArgumentException('Class name must not be empty.');
        } elseif (self::isBuiltinType($lower)) {
            return $lower;
        } elseif ($lower === 'self' || $lower === 'static' || $lower === '$this') {
            return $rc->getName();
        } elseif ($class[0] === '\\') { // fully qualified name
            return ltrim($class, '\\');
        }

        $uses = self::getUseStatements($rc);

        $parts = explode('\\', $class, 2);

        if (isset($uses[$parts[0]])) {
            $parts[0] = $uses[$parts[0]];

            return implode('\\', $parts);
        } elseif ($rc->inNamespace()) {
            return $rc->getNamespaceName() . '\\' . $class;
        } else {
            return $class;
        }
    }


    /**
     * Get map of class imports
     *
     * @param  ReflectionClass  $class
     * @return array of [alias => class]
     */
    public static function getUseStatements(ReflectionClass $class)
    {
        if (!isset(self::$cache[$name = $class->getName()])) {
            if ($class->isInternal()) {
                self::$cache[$name] = [];
            } else {
                $code = file_get_contents($class->getFileName());

                $useStatements = self::parseUseStatements($code, $name);

                // anonymous class name and the class returned from parseUseStatements are not same
                if (!isset($useStatements[$name])) {
                    self::$cache[$name] = array_merge(...array_values($useStatements));
                }

                self::$cache = $useStatements + self::$cache;
            }
        }
        return self::$cache[$name];
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isBuiltinType($type)
    {
        return in_array(
            strtolower($type),
            ['string', 'int', 'float', 'bool', 'array', 'callable', 'double', 'mixed', 'iterable', 'iterator', 'null'],
            true
        );
    }


    /**
     * Parses PHP code.
     *
     * @param $code
     * @param  null  $forClass
     * @return array of [class => [alias => class, ...]]
     */
    public static function parseUseStatements($code, $forClass = null)
    {
        $tokens = token_get_all($code);

        $namespace = $class = $classLevel = $level = null;

        $res = $uses = [];

        while ($token = current($tokens)) {
            next($tokens);
            switch (is_array($token) ? $token[0] : $token) {
                case T_NAMESPACE:
                    $namespace = ltrim(self::fetch($tokens, [T_STRING, T_NS_SEPARATOR]) . '\\', '\\');

                    $uses = [];

                    break;
                case T_CLASS:
                case T_INTERFACE:
                case T_TRAIT:
                    if ($name = self::fetch($tokens, T_STRING)) {
                        $class = $namespace . $name;

                        $classLevel = $level + 1;

                        $res[$class] = $uses;

                        if ($class === $forClass) {
                            return $res;
                        }
                    }
                    break;

                case T_USE:
                    while (!$class && ($name = self::fetch($tokens, [T_STRING, T_NS_SEPARATOR]))) {
                        $name = ltrim($name, '\\');

                        if (self::fetch($tokens, '{')) {
                            while ($suffix = self::fetch($tokens, [T_STRING, T_NS_SEPARATOR])) {
                                if (self::fetch($tokens, T_AS)) {
                                    $uses[self::fetch($tokens, T_STRING)] = $name . $suffix;
                                } else {
                                    $tmp = explode('\\', $suffix);

                                    $uses[end($tmp)] = $name . $suffix;
                                }
                                if (!self::fetch($tokens, ',')) {
                                    break;
                                }
                            }

                        } elseif (self::fetch($tokens, T_AS)) {
                            $uses[self::fetch($tokens, T_STRING)] = $name;

                        } else {
                            $tmp = explode('\\', $name);

                            $uses[end($tmp)] = $name;
                        }
                        if (!self::fetch($tokens, ',')) {
                            break;
                        }
                    }
                    break;

                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                case '{':
                    $level++;
                    break;

                case '}':
                    if ($level === $classLevel) {
                        $class = $classLevel = null;
                    }
                    $level--;
            }
        }

        return $res;
    }

    /**
     * @param $tokens
     * @param $take
     * @return string|null
     */
    private static function fetch(&$tokens, $take)
    {
        $res = null;
        while ($token = current($tokens)) {
            list($token, $s) = is_array($token) ? $token : [$token, $token];

            if (in_array($token, (array) $take, true)) {
                $res .= $s;
            } elseif (!in_array($token, [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT], true)) {
                break;
            }
            next($tokens);
        }

        return $res;
    }

}
