<?php
declare(strict_types=1);
namespace App\Repository\Virtual;

use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\VirtualRepository;
use App\Repository\Utils\DBManager;
use PDO;
use PDOException;

class VirtualtagRepository extends VirtualRepository {

    protected const MODEL_CLASS = VirtualtagModel::class;

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    /** @throws PDOException */
    public function insertNewTags(array $tagList): void {

        $sql = "INSERT IGNORE INTO tag(tag_string) VALUES ";
        $values = [];
        $len = count($tagList);

        for($i = 0; $i < $len; ++$i) {
            
            $sql .= ($i === ($len - 1))? ' (?);' : ' (?),';
            $values[$i + 1] = [(string)$tagList[$i], PDO::PARAM_STR];
        }

        $this->myDB->executeUpdate($sql, $values);      
    }

    /** @throws PDOException */
    public function findByTagStrings(array $tagStrings): array {

        $sql = "SELECT t.tag_id
                FROM tag t
                WHERE t.tag_string IN ( ";
        $values = [];
        $len = count($tagStrings);

        for ($i = 0; $i < $len; ++$i) {

            $sql .= ($i === ($len - 1)) ? ' ?' : ' ?,';
            $values[$i + 1] = [(string)$tagStrings[$i], PDO::PARAM_STR];
        }
        $sql .= " );";

        $res = $this->myDB->executeQuery($sql, $values);
        $result = $res->fetchAll(PDO::FETCH_COLUMN);

        return ($result) ?: [];
    }

    /** @throws PDOException */
    public function linkTagsToInventory(string $invId, array $tagIds): void {

        $sql = "INSERT INTO tag_inventory(inv, tag) VALUES ";
        $values = [];
        $len = count($tagIds);

        for ($i = 0; $i < $len; ++$i) {

            $sql .= ($i === ($len - 1)) ? ' (?,?);' : ' (?,?),';
            $values[($i * 2) + 1] = [$invId, PDO::PARAM_STR];
            $values[($i * 2) + 2] = [(int)$tagIds[$i], PDO::PARAM_INT];
        }

        $this->myDB->executeUpdate($sql, $values);     
    }

    /** @throws PDOException */
    public function findById(string $id) : VirtualtagModel | false {

        $sql = "SELECT GROUP_CONCAT(t.tag_string ORDER BY t.tag_string SEPARATOR ', ') as tag_list 
                FROM tag_inventory ti 
                INNER JOIN tag t ON (ti.tag = t.tag_id) 
                GROUP BY ti.inv 
                HAVING ti.inv = :id ";
        $values = [":id" => [$id, PDO::PARAM_STR]];

        $res = $this->myDB->executeQuery($sql, $values);

        $result = DBManager::fetchClass($res, $this->getModelClass(), '__set_state');

        return ($result) ?? false;
    }
}