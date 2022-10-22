<?php
declare(strict_types=1);
namespace App\Model\Virtual;

use App\Exception\InvalidArrayException;
use App\Model\Abstract\VirtualModel;
use App\Utils\MyCastUtils;
use App\Utils\MyReflectionUtils;

class VirtualtagModel extends VirtualModel {

    public const SEPARATOR = ',';

    /** @valid tag
     *  @pattern ~^(?=.*\w.*)(?!.*,?[ '\w\d]{51,})([ ,'\w\d]+)$~
    */
    protected string $tag_list;

    public static function parseTags(string $subject): array {

        $subject = trim($subject);
        return (preg_split('/\s*,\s*/', $subject, -1, PREG_SPLIT_NO_EMPTY))?: [];
    }

    /** Static method to create an object of the required Model Type
     * @override :
     *  - custom processing of tag string 
     *  - no fk processing
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

        if ($count === 0) {

            throw new InvalidArrayException("Cannot instantiate empty Model!");
        }

        if (($thorough) && (!$res->allPropsInitialized())) {

            throw new InvalidArrayException("Cannot instantiate non-conforming Model!");
        }

        return $res;
    }
}