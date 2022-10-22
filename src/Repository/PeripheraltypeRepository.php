<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\PeripheraltypeModel;
use App\Repository\Abstract\SecondaryRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class PeripheraltypeRepository extends SecondaryRepository {

    protected const SQL_START_FINDBYNAME = "SELECT * FROM peripheraltype p WHERE p.type_name ";
    protected const SQL_END_FINDBYNAME = " ORDER BY p.type_name";
    protected const MODEL_CLASS = PeripheraltypeModel::class;

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
    public function findById(int $id) : PeripheraltypeModel | false {

        $sql = "SELECT * FROM peripheraltype p WHERE p.type_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}