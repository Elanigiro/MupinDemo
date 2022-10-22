<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\SoftwaretypeModel;
use App\Repository\Abstract\SecondaryRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class SoftwaretypeRepository extends SecondaryRepository {

    protected const SQL_START_FINDBYNAME = "SELECT * FROM softwaretype s WHERE s.type_name ";
    protected const SQL_END_FINDBYNAME = " ORDER BY s.type_name";
    protected const MODEL_CLASS = SoftwaretypeModel::class;

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
    public function findById(int $id) : SoftwaretypeModel | false {

        $sql = "SELECT * FROM softwaretype s WHERE s.type_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}