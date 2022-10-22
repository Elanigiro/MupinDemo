<?php
declare(strict_types=1);
namespace App\Repository\Abstract;

use App\Alias\MyPDODataAlias;
use App\Model\Abstract\MainModel;
use App\Model\InventoryModel;
use App\Repository\Utils\DBManager;
use App\Repository\Virtual\VirtualtagRepository;
use App\Utils\MyArrayUtils;
use PDO;
use PDOException;

abstract class MainRepository extends RepositoryData {

    abstract static protected function getSearchProcedure(): string;
    abstract static protected function getAddProcedureId(): string;
    abstract static protected function getAddProcedure(): string;
    abstract static protected function buildAddParams(array $params): array;
    abstract static protected function getSqlStartFindByIds(): string;
    abstract static protected function getSqlStartFindAllLike(): string;
    /** @return array {[0]=>queryEnd, [1]=>queryParams} */
    abstract static protected function buildSqlLike(array $params): array;

    protected VirtualtagRepository $myTagRep;

    public function __construct(MyPDODataAlias $pdo) {

        $this->myDB = new DBManager($pdo);
        // same PDO instance => same connection => same transaction
        // Hence I don't need the VirtualtagRepository to manage the transactions
        $this->myTagRep = new VirtualtagRepository($pdo); 
    }

    /** @throws PDOException */
    public function searchProcedure(string $searchtxt): array {

        $sql = "CALL " . $this->getSearchProcedure() . "(:txt)";
        $values = [':txt' => [$searchtxt, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);
        $result = DBManager::fetchAllClassAsMap($res, $this->getModelClass(), '__set_state', 'getId');

        return $result;
    }

    /** 
     * @note If called without starting a transaction the stored procedure will implicitly start and commit/rollback one
     * @throws PDOException
    */
    public function addProcedure(array $params, array $tags): void {

        $sqlItem = "CALL " . $this->getAddProcedure();
        $valuesItem = $this->buildAddParams($params);
        $itemId = ($valuesItem[$this->getAddProcedureId()][0])?? null;

        $this->myDB->executeUpdate($sqlItem, $valuesItem);
        if (!empty($tags)) {

            if ($this->myTagRep->insertNewTags($tags) === false) {
                throw new PDOException("I could not add the new tags!");
            }
            $tmpTagIds = $this->myTagRep->findByTagStrings($tags);
            if (count($tmpTagIds) !== count($tags)) {
                throw new PDOException("Possible tag duplicates!");
            }
            if ($this->myTagRep->linkTagsToInventory($itemId, $tmpTagIds) === false) {
                throw new PDOException("I could not link the tags to the inventory item!");
            }
        }
    }

    /** @throws PDOException */
    public function findById(string $id): MainModel | false {

        $sql = $this->getSqlStartFindByIds() . "= :id";
        $values = [':id' => [$id, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }

    /** @throws PDOException */
    public function findAllByIds(array $ids): array {
        
        $sql = ($this->getSqlStartFindByIds() . " IN (" . implode(",", array_fill(0, count($ids), "?")). ") ");
        $ids = MyArrayUtils::array_walk_processed_copy($ids, function($k, $v) { return [$k + 1, [$v, PDO::PARAM_STR]]; });
        $values = $ids;

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClassAsMap($res, $this->getModelClass(), '__set_state', 'getId');

        return $result;
    }

    /** @throws PDOException  */
    public function findAllLike(array $params): array {

        $sql = $this->getSqlStartFindAllLike();
        $strAndValues = $this->buildSqlLike($params);
        $sql .= $strAndValues[0];

        $values = $strAndValues[1];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClass($res, $this->getModelClass(), '__set_state');

        return $result;
    }
}