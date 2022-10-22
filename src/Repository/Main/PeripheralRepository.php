<?php
declare(strict_types=1);
namespace App\Repository\Main;

use App\Model\Main\PeripheralModel;
use App\Repository\Abstract\MainRepository;
use PDO;

class PeripheralRepository extends MainRepository {

    protected const SQL_START_FINDALL_BYIDS = "SELECT * FROM peripheral p WHERE p.periph_id ";
    protected const SQL_START_FINDALL_LIKE = "SELECT p.periph_id, p.company, p.model, p.type, p.notes, p.url  
                                              FROM peripheral p 
                                              LEFT OUTER JOIN tag_inventory ti ON p.periph_id = ti.inv
                                              LEFT OUTER JOIN tag t ON ti.tag = t.tag_id
                                              WHERE ";
    protected const SQL_END_FINDALL_LIKE = " GROUP BY p.periph_id ORDER BY p.model ASC ";
    protected const MODEL_CLASS = PeripheralModel::class;
    protected const SEARCH_ST_PROC = "search_peripheral";
    protected const ADD_ST_PROC_ID = ":id";
    protected const ADD_ST_PROC = "add_peripheral(" . self::ADD_ST_PROC_ID . ", :company_name, :model, :type_name, :notes, :url)";

    protected static function getAddProcedureId(): string {

        return static::ADD_ST_PROC_ID;
    }

    protected static function getSearchProcedure(): string {

        return static::SEARCH_ST_PROC;
    }

    protected static function getAddProcedure(): string {

        return static::ADD_ST_PROC;
    }

    protected static function getModelClass(): string {

        return static::MODEL_CLASS;
    }

    protected static function getSqlStartFindAllLike(): string {

        return static::SQL_START_FINDALL_LIKE;
    }

    protected static function getSqlStartFindByIds(): string {

        return static::SQL_START_FINDALL_BYIDS;
    }

    protected static function buildAddParams(array $params): array {

        $vals[':id'] = [$params['periph_id'], PDO::PARAM_STR];
        $vals[':company_name'] = [$params['company_name'], PDO::PARAM_STR];
        $vals[':model'] = [$params['model'], PDO::PARAM_STR];
        $vals[':type_name'] = [$params['type_name'], PDO::PARAM_STR];
        $vals[':notes'] = (isset($params['notes'])) ? [$params['notes'], PDO::PARAM_LOB] : [null, PDO::PARAM_NULL];
        $vals[':url'] = (isset($params['url'])) ? [$params['url'], PDO::PARAM_STR] : [null, PDO::PARAM_NULL];

        return $vals;
    }

    protected static function buildSqlLike(array $params): array {

        // $sql will contain the WHERE conditions and ? placeholders
        $sql = "";
        // $vals will contain the bind values for said placeholders in the correct order
        $vals = [];

        // bindValue requires 1-indexed parameters
        $index = 1;
        foreach ($params as $key => $value) {

            if (strlen($sql) != 0) {

                $sql .= " AND ";
            }
            
            switch ($key) {

                case 'company':
                case 'type':
                    $sql .= " p." . $key . " IN (" . implode(",", array_fill(0, count($value), "?")). ") ";
                    foreach ($value as $x) {
                        
                        $vals[$index++] = [$x, PDO::PARAM_INT];
                    }
                    break;

                case 'tags':
                    $sql .= " t.tag_string LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
                
                default:
                    $sql .= " p." . $key . " LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
            }
        }

        $sql .= static::SQL_END_FINDALL_LIKE;

        return [$sql, $vals];
    }
}