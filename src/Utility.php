<?php
namespace Onionimbus\System;

class Utility
{
    /**
     * Apply a function to each member of an array
     * 
     * @param array $array
     * @param callable $func
     * @return array
     */
    public static function map($array, $func)
    {
        if (!is_callable($func)) {
            return $array;
        }
        foreach ($array as $i => $v) {
            if (is_array($array[$i])) {
                $array[$i] = self::map($array[$i], $func);
            } else {
                $array[$i] = $func($v);
            }
        }
        return $array;
    }
    
    /**
     * Reduce an array to a single value
     * 
     * @param array $array
     * @param callable $func
     * @param mixed $context
     * @return mixed
     */
    public static function reduce($array, $func, $context = null)
    {
        foreach ($array as $item) {
            $context = $func($item, $context);
        }
        return $context;
    }
    /**
     * Read a local JSON file as an array
     * 
     * @param string $file
     * @return array
     */
    public static function getJSON($file)
    {
        if (\is_readable($file)) {
            return self::parseJSON(\file_get_contents($file), true);
        }
    }
    
    /**
     * Parse a JSON string (with support for Javascript style comments)
     * 
     * @param string $json
     * @param boolean $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public static function parseJSON($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode(
            preg_replace(
                "#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#",
                '',
                $json
            ),
            $assoc,
            $depth,
            $options
        );
    }
}
