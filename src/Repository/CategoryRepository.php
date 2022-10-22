<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\CategoryModel;
use App\Repository\Abstract\RepositoryData;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class CategoryRepository extends RepositoryData {

    protected const MODEL_CLASS = CategoryModel::class;

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    /**
     * @return array with all records found. Empty array in case of nothing found
     * @throws PDOException
     * 
     */
    public function findAll() : array {

        $sql = "SELECT * FROM category c ORDER BY c.table_name ASC";
        $values = [];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClass($res, $this->getModelClass(), '__set_state');

        return $result;
    }

    /** @throws PDOException */
    public function findById(int $id): CategoryModel | false {

        $sql = "SELECT * FROM category c WHERE c.cat_id = :id";
        $values = [":id" => [$id, PDO::PARAM_INT]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}