<?php
declare(strict_types=1);
namespace App\Utils;

class MyArrayUtils {

    /**
    * @param callable $foo must be a callable object that takes 2 arguments and returns a bool
    * @return int first index where needle was found. Note: unlike array_filter this method does not return an array
    * @return false if not found
    */
    public static function array_search_callable(mixed $needle, array $haystack, callable $foo) : int | false {

        for ($i=0; $i < count($haystack); $i++) { 
            
            if ($foo($haystack[$i], $needle) === true) {

                return $i;
            }
        }

        return false;
    }

    /** @param callable $foo MUST take two parameters (key,value) and return an array of 2 elements [key_processed, value_processed] */
    public static function array_walk_processed_copy(array $origin, callable $foo): array {

        $res = [];
        foreach ($origin as $key => $value) {
            
            $returnedPair = $foo($key, $value);
            $res[$returnedPair[0]] = $returnedPair[1];
        }

        return $res;
    }

    public static function getArrayFromField(array $a, string $fieldName) : array {

        $resArray = [];

        foreach ($a as $obj) {
            $resArray[] = $obj->{$fieldName};
        }

        return $resArray;
    }

    /**
     * @return mixed first item which satisfies the pattern, false otherwise
     */
    public static function preg_first(array $a, string $pattern) : mixed {
        
        $resArray = preg_grep($pattern, $a);
        return reset($resArray);
    }
}