<?php
declare(strict_types=1);
namespace App\Utils;

use Stringable;
use ValueError;

class MyCastUtils {

    /**
     * Set the type of a variable
     * @param mixed &$var The variable being converted.
     * 
     * @param string $type
     * Possible values of type are:
     * 
     * - "boolean" (or "bool")
     * - "integer" (or "int")
     * - "float"
     * - "string"
     * - "array"
     * - "object"
     * @return bool true on success or false on failure.
     * @throws ValueError if non-valid type argument
     * @note strict conversion of strings to int/float
     */
    public static function settype(mixed &$var, string $type): bool {

        switch ($type) {

            case 'boolean':
            case 'bool':
                $var = filter_var($var, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                return ($var === null)? false : true;
                break;
            
            case 'integer':
            case 'int':
                $var = filter_var($var, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                return ($var === null)? false : true;
                break;

            case 'float':
                $var = filter_var($var, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                return ($var === null)? false : true;
                break;

            case 'string':
                if (self::isStringable($var)) {

                    $var = (string)$var;
                    return true;
                }
                else {

                    return false;
                }
                break;

            case 'array':
                return settype($var, $type);
                break;

            case 'object':
                return settype($var, $type);
                break;
            
            default:
                throw new ValueError("Invalid type!");
                break;
        }
    }

    public static function isStringable(mixed $var): bool {

        return (is_scalar($var)) || ($var instanceof Stringable);
    }
}