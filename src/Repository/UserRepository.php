<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\UserModel;
use App\Repository\Abstract\RepositoryLogin;
use App\Repository\Utils\DBManager;
use DateTime;
use DateTimeZone;
use PDO;
use PDOException;

class UserRepository extends RepositoryLogin {

    /** @throws PDOException */
    public function findAll() : array {

        $sql = "SELECT * FROM user u";
        $values = [];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClass($res, UserModel::class, '__set_state');

        return $result;
    }

    /** @throws PDOException */
    public function findByUsrId(int $id) : UserModel | false {

        $sql = "SELECT * FROM user u WHERE u.user_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, UserModel::class, '__set_state');

        return ($result) ?? false;
    }

    /** @throws PDOException */
    public function findByUsrEmail(string $email) : array {

        $sql = "SELECT * FROM user u WHERE u.email = :email";
        $values = [":email" => [$email, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClass($res, UserModel::class, '__set_state');

        return $result; 
    }

    /** @throws PDOException */
    public function insertUser(string $email, string $pwd) : void {

        $sql = "INSERT INTO user(email,pwd,first_access) VALUES (:email,:pwd,:first_access)";
        $currentUTC = new DateTime("now", new DateTimeZone("UTC"));
        $currentUTC = $currentUTC->format('Y-m-d H:i:s.v');
        $values = [ ":email" => [$email, PDO::PARAM_STR],
                    ":pwd" => [$pwd, PDO::PARAM_STR], 
                    ":first_access" => [$currentUTC, PDO::PARAM_STR]
                  ];

        $this->myDB->executeUpdate($sql, $values); 
    }

    /** @throws PDOException */
    public function deactivateUser(int $id) : void {

        $sql = "UPDATE user u SET deactivated = :deactivated, pwd = '0' WHERE u.user_id = :id";
        $currentUTC = new DateTime("now", new DateTimeZone("UTC"));
        $currentUTC = $currentUTC->format('Y-m-d H:i:s');
        $values = [ ":deactivated" => [$currentUTC, PDO::PARAM_STR],
                    ":id" => [$id, PDO::PARAM_INT]
                  ];

        $this->myDB->executeUpdate($sql, $values);  
    }
}