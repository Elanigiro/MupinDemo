<?php
declare(strict_types=1);
namespace App\Utils;

use App\Model\Virtual\VirtualtagModel;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class MyReflectionUtils {

    public const DEFAULT_PATTERN = '^(.*)$';

    /**
     * @return array empty if class/object not found or no property satisfies filter
     */
    public static function getProperties(object|string $objectOrClass, int|null $filter = null) : array {
        
        try {

            $reflect = new ReflectionClass($objectOrClass);
            return $reflect->getProperties($filter);
        }
        catch (ReflectionException) { 

            return [];
        }
    }

    public static function getShortClassName(object|string $objectOrClass): string {

        return (new ReflectionClass($objectOrClass))->getShortName();
    }

    public static function getShortModelName(object|string $objectOrClass): string {

        return strtolower(preg_replace('/Model$/', "", self::getShortClassName($objectOrClass)));
    }

    /**
     * @return array empty if class/object not found or no property satisfies filter
     */
    public static function getPropertiesName(object|string $objectOrClass, int|null $filter = null) : array {
        
        try {

            $reflect = new ReflectionClass($objectOrClass);
            $propsArray = $reflect->getProperties($filter);
    
            $resArray = [];
            foreach ($propsArray as $prop) {
                
                $resArray[] = $prop->getName();
            }
            return $resArray;
        }
        catch (ReflectionException) { 

            return [];
        }
    }

    /**
     * @return string "null" as Fallback
     */
    public static function getPropertyType(object|string $objectOrClass, string $property) : string {

        try {

            $prop = new ReflectionProperty($objectOrClass, $property);
            $type = $prop->getType();
            
            return $type->getName();
        }
        catch (ReflectionException) {

            return "null";
        }
    }

    /**
     * @return array empty if class/object not found or no property satisfies filter/pattern
     */
    public static function regexPropertyNames(object|string $objectOrClass, string $pattern, int|null $filter = null) : array {
        
        $propArray = self::getPropertiesName($objectOrClass, $filter);
        $resArray = preg_grep($pattern, $propArray);
        // array_values removes any "gaps" from the array indices
        return array_values($resArray);
    }

    /**
     * @return string empty if no Documentation Comment found
     */
    public static function getPropertyDoc(object|string $objectOrClass, string $property) : string {

        $prop = new ReflectionProperty($objectOrClass, $property);

        return ($prop->getDocComment())?: "";
    }

    /**
     * @return array of 3 elements ['type', 'min', 'max', 'pattern']
     *  - min === 'inf' => -infinity
     *  - max === 'inf' => +infinity
     */
    public static function getValidationInfo(object|string $objectOrClass, string $property): array {

        $doc = MyReflectionUtils::getPropertyDoc($objectOrClass, $property);

        if (preg_match('/@valid\s+(\w+)\s+min\s+(\d+|inf)\s+max\s+(\d+|inf)/m', $doc, $rawResult)) {

            $res = [$rawResult[1], $rawResult[2], $rawResult[3]];
        } else if (preg_match('/@valid\s+(\w+)\s+/m', $doc, $rawResult)) {

            $res = [$rawResult[1], '0', 'inf'];

            // in case of a number
            if (($rawResult[1] === 'int') || ($rawResult[1] === 'bcnum')) {

                $res[1] = 'inf'; // stands for minus infinity if no range validation required
            }

        } else {

            $res = ['string', '0', 'inf'];
        }

        if (preg_match('/@pattern\s+~(.+)~/m', $doc, $rawResult)) {

            $res[] = $rawResult[1];
        } else {

            $res[] = self::DEFAULT_PATTERN;
        }

        return $res;
    }

    /** It validates the property based on the corresponding doc comment.
     * @note It is assumed that the property exists and is initialized
     * @param bool $thorough if false the method only checks the type compatibility if true it also checks the value conformity
     * @warning the property must be Stringable
    */
    public static function isValidProp(object $obj, string $property, bool $thorough) : bool {

        $value = $obj->{$property};
        $validationInfo = self::getValidationInfo($obj, $property);

        $type = $validationInfo[0];
        $min = $validationInfo[1];
        $max = $validationInfo[2];
        $pattern = $validationInfo[3];

        //can only evaluate strings
        if (!MyCastUtils::settype($value, 'string')) {

            return false;
        }

        // pattern check
        if ($thorough) {

            if (!preg_match('/' . $pattern . '/', $value)) {

                return false;
            }
        }

        switch ($type) {

            case 'string':
            case 'textarea':
                if ($thorough) {

                    if (((MyCastUtils::settype($min, 'int')) && (strlen($value) < $min)) || ((MyCastUtils::settype($max, 'int')) && (strlen($value) > $max))) {

                        return false;
                    }
                }
                break;

            case 'url':
                if ($thorough) {

                    if (filter_var($value, FILTER_VALIDATE_URL) === false) {

                        return false;
                    }
                }
                break;

            case 'int':
                // check that the type is compatible
                if (!MyCastUtils::settype($value, $type)) {

                    return false;
                }
                if ($thorough) {

                    if (((MyCastUtils::settype($min, 'int')) && ($value < $min)) || ((MyCastUtils::settype($max, 'int')) && ($value > $max))) {

                        return false;
                    }
                }
                break;

            case 'bcnum':
                // check that the type is compatible
                if (!ctype_digit($value)) {

                    return false;
                }
                if ($thorough) {

                    if ((($min !== 'inf') && (bccomp($value, $min) < 0)) || (($max !== 'inf') && (bccomp($value, $max) > 0))) {

                        return false;
                    }
                }
                break;

            case 'email':
                if ($thorough) {

                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {

                        return false;
                    }
                }
                break;

            case 'pwd':
                if ($thorough) {

                    if (!MyPasswordUtils::validPwd($value)) {

                        return false;
                    }
                }
                break;

            case 'tag':
                // already checked
                break;

            case 'notinitialized':
            default: // invalid $type
                if ($thorough) {

                    return false; // it isn't supposed to be initialized
                }
                break;
        }

        return true;
    }
}