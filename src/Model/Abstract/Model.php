<?php
declare(strict_types=1);
namespace App\Model\Abstract;

use App\Exception\InvalidArrayException;
use App\Utils\MyCastUtils;
use App\Utils\MyReflectionUtils;

/** This class represents a DB entity model.
 * There are 3 ways to instantiate a Model object:
 * 1) "factoryFromArray($array)": the preferred method to instantiate a Model for a DB Search. It only performs basic type validation.
 * 2) "factoryFromArray($array, true)": the preferred method to instantiate a Model for a DB Update/Insert. It performs complete property validation.
 * 3) "__set_state($array)": the preferred method to instantiate a Model for DB record retrieval. It performs no validation.
*/
abstract class Model {

    // the object can only be instantiated through factory method (or through reflection, I guess)
    protected function __construct() {}

    /** Static method to create an object of the required Model Type
     * @throws InvalidArrayException in case of failed validation or empty class
     * @param bool $thorough if 'false' the method only checks the type compatibility if 'true' it also checks the value conformity
     * @note use false for searches true for DB management
     */
    public static function factoryFromArray(array $a, bool $thorough = false) : static {

        $res = new static();
        $count = 0;

        foreach ($a as $key => $value) {

            if (!property_exists($res::class, $key)) {

                throw new InvalidArrayException("Invalid field: " . $key);
            }

            // skip empty values
            if (empty($value)) {

                continue;
            }

            // advanced processing in case of FK property (in this case $value MUST be string compatible)
            $fk = static::isFK($key);
            if ($fk) {

                $validationInfo = MyReflectionUtils::getValidationInfo($res, $key);
                // in case of NON thorough validation I just put everything in one bucket
                $tmpPattern = ($thorough)? $validationInfo[3] : MyReflectionUtils::DEFAULT_PATTERN;

                // ($thorough == true) => the pattern should have as many capturing groups as count($fk['GET'])
                $pattern = '/' . $tmpPattern . '/';

                // Matches
                preg_match($pattern, (string)$value, $matched);

                $tmpArray = [];
                for ($i=0; $i < count($fk['GET']); $i++) { 
                    //NOTE: capturing groups are 1-indexed => $matched[$i + 1]
                    $tmpArray[$fk['GET'][$i]] = ($matched[$i + 1])?? '';
                }

                $res->{$key} = call_user_func([MyReflectionUtils::getPropertyType($res, $key), 'factoryFromArray'], $tmpArray, $thorough);
                ++$count;
            }
            else {

                // if cast fails
                if (!MyCastUtils::settype($value, MyReflectionUtils::getPropertyType($res::class, $key))) {

                    throw new InvalidArrayException("Invalid type for field: " . $key);
                }

                $res->{$key} = $value;

                // if validation fails
                if (!MyReflectionUtils::isValidProp($res, $key, $thorough)) {

                    throw new InvalidArrayException("Invalid value for field: " . $key);
                }

                ++$count;
            }
        }

        if ($count === 0) {

            throw new InvalidArrayException("Cannot instantiate empty Model!");
        }

        
        if (($thorough) && (!$res->allPropsInitialized())) {

            throw new InvalidArrayException("Cannot instantiate non-conforming Model!");
        }

        return $res;
    }

    /** If the $property is an FK it extracts the required fields (2 max) as concatenated strings.
     * @note This method is only called for inaccessible (private, protected) or non-existing properties
     * @note They are "inaccessible" only outside the class scope.
     */
    public function __get(string $property) {

        if ((property_exists($this, $property)) && (isset($this->{$property}))) {

            $fk = $this->isFK($property);
            if ($fk) {
                
                $fkParams = $fk['GET'];
                $fkObj = $this->{$property};

                $res = (string)($fkObj->{$fkParams[0]});

                for ($i=1; ($i < count($fkParams)) && (isset($fkObj->{$fkParams[$i]})); $i++) { 
                    
                    $res .= (", " . (string)($fkObj->{$fkParams[$i]}));
                }                

                return $res;
            }
        }

        return $this->{$property};
    }

    /** Checks that all non-dynamic properties are initialized
     * @note nullables can be null
     * @param bool $excludePK if true this method won't check that PK is initialized
    */
    protected function allPropsInitialized(bool $excludePK = true): bool {

        $props = MyReflectionUtils::getProperties($this);

        foreach ($props as $prop) {

            if (($excludePK) && ($this->isPK($prop->getName()))) {

                continue;
            }

            // necessary to let isInitialized() access protected/private properties (on PHP < 8.1.0)
            $prop->setAccessible(true);
            
            if (!$prop->isInitialized($this)) {

                return false;
            }
        }

        return true;
    }

    /** Factory from array without Validation
     * @warning use it for data fetched from the DB ONLY
     */
    public static function __set_state(array $properties) : static {
        
        $res = new static();

        foreach ($properties as $prop => $value) {

            if (property_exists($res, $prop)) {

                $fk = static::isFK($prop);
                if ($value === null) {
                    
                    $res->{$prop} = $value;
                }
                else if ($fk) {
    
                    $res->{$prop} = call_user_func([MyReflectionUtils::getPropertyType($res, $prop), '__set_state'], [$fk['SET'] => $value]);
                }
                else {
    
                    // cast to correct type
                    MyCastUtils::settype($value, MyReflectionUtils::getPropertyType($res::class, $prop));
                    $res->{$prop} = $value;
                }
            }
            // 'null' identifies a virtual property => it does not exist in the real model
            else if ($prop !== 'null') {

                // Dynamic additional property (e.g. 'relevance')
                $res->{$prop} = $value;
            }
        }

        return $res;
    }

    /**
     * @warning this method is empty. Override it in subclasses in order to implement it.
     */
    public function refresh(array $fkServices): void {}

    /** It allows the use of isset on inaccessible properties.
     * @note This method is only called for inaccessible (private, protected) or non-existing properties
     * @note They are "inaccessible" only outside the class scope.
     */
    public function __isset(string $property) {

        if (property_exists($this, $property)) {

            return isset($this->{$property});
        }

        return false;
    }

    public static function isFK(string $property) : array | false {

        $doc = MyReflectionUtils::getPropertyDoc(static::class, $property);

        if (preg_match('/@fk\s+GET\s+(\S+)\s+SET\s+(\w+)/m', $doc, $rawResult)) {

            return ['GET' => explode(',', $rawResult[1]), 'SET' => $rawResult[2]];
        }

        return false;
    }

    public static function isPK(string $property) : bool {

        $doc = MyReflectionUtils::getPropertyDoc(static::class, $property);

        if (preg_match('/@pk/m', $doc)) {

            return true;
        }

        return false;
    }

    public static function isUrl(string $property) : bool {

        $doc = MyReflectionUtils::getPropertyDoc(static::class, $property);

        if (preg_match('/@valid\s+url/m', $doc)) {

            return true;
        }

        return false;
    }
}