<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\PublisherModel;
use App\Repository\Abstract\SecondaryRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class PublisherRepository extends SecondaryRepository {

    protected const SQL_START_FINDBYNAME = "SELECT * FROM publisher p WHERE p.pub_name ";
    protected const SQL_END_FINDBYNAME = " ORDER BY p.pub_name";
    protected const MODEL_CLASS = PublisherModel::class;

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
    public function findById(int $id) : PublisherModel | false {

        $sql = "SELECT * FROM publisher p WHERE p.pub_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}