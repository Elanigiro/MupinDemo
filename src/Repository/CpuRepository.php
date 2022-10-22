<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\CpuModel;
use App\Repository\Abstract\SecondaryRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class CpuRepository extends SecondaryRepository {

    protected const SQL_START_FINDBYNAME = "SELECT * FROM cpu c WHERE c.cpu_name ";
    protected const SQL_END_FINDBYNAME = " ORDER BY c.cpu_name";
    protected const MODEL_CLASS = CpuModel::class;

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    protected static function getSqlStartFindByName(): string {

        return static::SQL_START_FINDBYNAME;
    }

    protected static function getSqlEndFindByName(): string {

        return static::SQL_END_FINDBYNAME;
    }

    /** @throws PDOExcpetion */
    public function findById(int $id) : CpuModel | false {

        $sql = "SELECT * FROM cpu c WHERE c.cpu_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}