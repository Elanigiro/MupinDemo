<?php
declare(strict_types=1);
namespace App\Model;

use App\Exception\InvalidArrayException;
use App\Model\Abstract\Model;
use App\Utils\MyCastUtils;
use App\Utils\MyPasswordUtils;
use App\Utils\MyReflectionUtils;

class UserModel extends Model {

    public const ACTIVE = '0000-00-00 00:00:00';

    /** @pk */
    protected int $user_id;
    
    /** @valid email */
    protected string $email;
    /** @valid pwd */
    protected string $pwd;
    /** @valid notinitialized */
    protected string $first_access;
    /** @valid notinitialized */
    protected ?string $deactivated = self::ACTIVE;

    /** Static method to create an object of the required Model Type
     * @throws InvalidArrayException in case of invalid/absent email/password
     * @param bool $hashed set if pwd must be hashed on construction - default: true
     * @param bool $thorough set if pwd must be validated - default: true
     * @note overrides the Model class static method
     */
    public static function factoryFromArray(array $a, bool $thorough = true, bool $hashed = true) : static {

        $res = new static();

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
        }

        if (!isset($res->email)) {

            throw new InvalidArrayException("Email must be provided!");
        }
        else if (!isset($res->pwd)) {

            throw new InvalidArrayException("Password must be provided!");
        }

        // hash the pwd if requested
        if ($hashed) {
            
            $res->hashed();
        }
        
        return $res;
    }

    public function hashed() : void {

        $this->pwd = MyPasswordUtils::hashPwd($this->pwd);
    }
}