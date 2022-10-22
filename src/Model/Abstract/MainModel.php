<?php
declare(strict_types=1);
namespace App\Model\Abstract;

use App\Utils\MyReflectionUtils;

/**
 * Identifies an object from the Museum's inventory
*/
abstract class MainModel extends Model {

    public const ID_LEN = 20;
    public const ID_POOL = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    public static function idGen() : string {

        $res = "";

        for ($i=0; $i < self::ID_LEN; $i++) { 
            
            $res .= self::ID_POOL[rand(0, strlen(self::ID_POOL) - 1)];
        }

        return $res;
    }

    /** @override */
    public static function isPK(string $property): bool {

        if ($property === static::getIdName()) {

            return true;
        }

        return false;
    }

    abstract public function getId() : string;
    abstract static public function getIdName() : string;
}