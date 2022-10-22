<?php
declare(strict_types=1);
namespace App\Repository\Abstract;

use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

abstract class SecondaryRepository extends RepositoryData {

    abstract static protected function getSqlStartFindByName(): string;
    abstract static protected function getSqlEndFindByName(): string;

    /** @throws PDOException */
    public function findByNameLike(string $name): array {

        $sql = $this->getSqlStartFindByName() . " LIKE CONCAT('%', :name, '%') " . $this->getSqlEndFindByName();
        $values = [":name" => [$name, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchAllClass($res, $this->getModelClass(), '__set_state');

        return $result;
    }
}