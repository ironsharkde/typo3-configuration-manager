<?php
/**
 * Created by PhpStorm.
 * User: antonpauli
 * Date: 11/02/16
 * Time: 12:48
 */

namespace IronShark\Typo3ConfigurationManager;


class Helper
{
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }
        return $results;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if ((!is_array($array) || !array_key_exists($segment, $array)) &&
                (!$array instanceof ArrayAccess || !$array->offsetExists($segment))
            ) {
                return static::value($default);
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? static::$value() : $value;
    }


    /**
     * Check if string is valid JSON
     *
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Replace value within config file recursive
     * @param $array
     * @param $path
     * @param $value
     * @return array|string
     */
    public static function arrayPathReplace($array, $path, $value)
    {
        $firstSegment = strstr($path, '.', true);
        $nextPath = substr(strstr($path, '.'), 1);

        // last level reached, write value
        if (strpos($path, '.') === false) {
            // replace values
            if (static::isSerialized($array)) {
                // unserialize => replace => serialize
                $unserialized = unserialize($array);
                $unserialized[$path] = $value;
                return serialize($unserialized);
            } else {
                $array[$path] = $value;
                return $array;
            }
        }

        // unwrap, request replacement, wrap
        if (static::isSerialized($array)) {
            $unserialized = unserialize($array);
            $unserialized[$firstSegment] = static::arrayPathReplace($unserialized[$firstSegment], $nextPath, $value);
            $array = serialize($unserialized);
        } else {
            if (is_array($array)) {

                if (isset($array[$firstSegment])) {
                    $array[$firstSegment] = static::arrayPathReplace($array[$firstSegment], $nextPath, $value);
                } else {
                    // TODO: create items recursive
                    $array[$firstSegment][$nextPath] = $value;
                }

            } else {
                $array = [];
                $array[$firstSegment] = $value;
            }
        }

        return $array;
    }


    /**
     * Tests if an input is valid PHP serialized string.
     *
     * Checks if a string is serialized using quick string manipulation
     * to throw out obviously incorrect strings. Unserialize is then run
     * on the string to perform the final verification.
     *
     * Valid serialized forms are the following:
     * <ul>
     * <li>boolean: <code>b:1;</code></li>
     * <li>integer: <code>i:1;</code></li>
     * <li>double: <code>d:0.2;</code></li>
     * <li>string: <code>s:4:"test";</code></li>
     * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
     * <li>object: <code>O:8:"stdClass":0:{}</code></li>
     * <li>null: <code>N;</code></li>
     * </ul>
     *
     * @author        Chris Smith <code+php@chris.cs278.org>
     * @copyright    Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
     * @license        http://sam.zoy.org/wtfpl/ WTFPL
     * @param        string $value Value to test for serialized form
     * @param        mixed $result Result of unserialize() of the $value
     * @return        boolean            True if $value is serialized data, otherwise false
     */
    public static function isSerialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value)) {
            return false;
        }
        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            $result = false;
            return true;
        }
        $length = strlen($value);
        $end = '';

        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':') {
                    return false;
                }
                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;
            default:
                return false;
        }
        if (($result = @unserialize($value)) === false) {
            $result = null;
            return false;
        }
        return true;
    }

    /**
     * Unserialize array recursive
     * @param $val
     * @return mixed|string
     */
    public static function unserializeRecursive($val)
    {
        if (!empty($val) && static::isSerialized($val)) {
            $val = trim($val);
            $ret = unserialize($val);
            if (is_array($ret)) {
                foreach ($ret as &$r) {
                    $r = static::unserializeRecursive($r);
                }
            }
            return $ret;
        } elseif (is_array($val)) {
            foreach ($val as &$r) {
                $r = static::unserializeRecursive($r);
            }
            return $val;
        } else {
            return $val;
        }
    }
}