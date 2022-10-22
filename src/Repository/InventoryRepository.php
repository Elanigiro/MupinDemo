<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\InventoryModel;
use App\Repository\Abstract\RepositoryData;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class InventoryRepository extends RepositoryData {

    protected const MODEL_CLASS = InventoryModel::class;

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    /** @throws PDOException */
    public function searchProcedure(string $searchtxt, string $catname) : array {

        $sql = "CALL search_tag_catid(:txt, :catname)";
        $values = [':txt' => [$searchtxt, PDO::PARAM_STR], ':catname' => [$catname, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);
        $result = DBManager::fetchAllClass($res, $this->getModelClass(), '__set_state');

        return $result;
    }

    /** 
     * @throws PDOException
     */
    public function deleteItem(string $id): void {

        $sqlItem = "DELETE FROM inventory WHERE inv_id = :id";
        $valuesItem = [':id' => [$id, PDO::PARAM_STR]];

        if ($this->myDB->executeUpdate($sqlItem, $valuesItem) !== 1) {

            throw new PDOException("Delete failed");
        }
    }

    /** @throws PDOException */
    public function findById(string $id): InventoryModel | false {

        $sql = "SELECT * FROM inventory i WHERE i.inv_id = :id";
        $values = [":id" => [$id, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}