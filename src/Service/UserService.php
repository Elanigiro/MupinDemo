<?php
declare(strict_types=1);
namespace App\Service;

use App\Exception\UnauthorizedException;
use App\Repository\UserRepository;
use App\Model\UserModel;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class UserService extends Service {

    protected UserRepository $myRep;

    public function __construct(UserRepository $urep) {

        $this->myRep = $urep;
    }

    public function fetchUserById(int $id) : UserModel | null {

        try {

            $res = $this->myRep->findByUsrId($id);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = null;
        }

        return $res;
    }

    public function fetchAllActiveUsers() : array {

        try {

            $tmpRes = $this->myRep->findAll();
          
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $tmpRes = [];
        }

        
        $res = [];
        foreach ($tmpRes as $usr) {

            if ($usr->deactivated === UserModel::ACTIVE) {

                $res[] = $usr;
            }
        }

        return $res;
    }

    public function fetchActiveUserByEmail(string $email) : UserModel | null {

        try {

            $tmpRes = $this->myRep->findByUsrEmail($email);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $tmpRes = [];
        }

        foreach ($tmpRes as $usr) {

            if ($usr->deactivated === UserModel::ACTIVE) {

                return $usr;
            }
        }

        return null;
    }

    public function addNewUser(UserModel $user, bool $transactional = true) : bool {

        try {
            if ($transactional) {

                $this->myRep->beginTransaction();
            }    

            $this->myRep->insertUser($user->email, $user->pwd);
        
            if ($transactional) {

                $this->myRep->commitTransaction();
            }            
        
            return true;
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
        
            if ($transactional) {

                $this->myRep->rollbackTransaction();
            }            
            // false => duplicate record
            return false;
        }
    }

    public function deleteUser(UserModel $user, bool $transactional = true) : bool {


        $found = $this->checkUserPassword($user);
        if ($found === null) {

            return false;
        }

        try {
            if ($transactional) {

                $this->myRep->beginTransaction();
            }

            $this->myRep->deactivateUser($found->user_id);
        
            if ($transactional) {

                $this->myRep->commitTransaction();
            }            
            
            return true;
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
        
            if ($transactional) {

                $this->myRep->rollbackTransaction();
            }
            return false;
        }
    }

    /**
     * @return UserModel if usr/pwd correct it returns the found user with complete info
     * @throws UnauthorizedException if wrong password or no corresponding user found
     */
    public function checkUserPassword(UserModel $user) : UserModel {

        $found = $this->fetchActiveUserByEmail($user->email);

        if ($found === null) {

            throw new UnauthorizedException("User \"$user->email\" not found!");
        }

        if(!password_verify($user->pwd, $found->pwd)) {

            throw new UnauthorizedException("Wrong Password for user \"$user->email\"");
        }

        return $found;
    }
}