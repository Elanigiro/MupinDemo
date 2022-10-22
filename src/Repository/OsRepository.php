<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\OsModel;
use App\Repository\Abstract\SecondaryRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class OsRepository extends SecondaryRepository {

    protected const SQL_START_FINDBYNAME = "SELECT * FROM os o WHERE CONCAT(o.os_name, ' ', o.os_version) ";
    protected const SQL_END_FINDBYNAME = " ORDER BY o.os_name";
    protected const MODEL_CLASS = OsModel::class;

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    protected static function getSqlStartFindByName(): string {

        return static::SQL_START_FINDBYNAME;
    }

    protected static function getSqlEndFindByName(): string {

        return static::SQL_END_FINDBYNAME;
    }

    /** @throws PDOException */
    public function findById(int $id) : OsModel | false {

        $sql = "SELECT * FROM os o WHERE o.os_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}