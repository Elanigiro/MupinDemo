<?php
declare(strict_types=1);
namespace App\Repository\Main;

use App\Model\Main\SoftwareModel;
use App\Repository\Abstract\MainRepository;
use PDO;

class SoftwareRepository extends MainRepository {

    protected const SQL_START_FINDALL_BYIDS = "SELECT * FROM software s WHERE s.software_id ";
    protected const SQL_START_FINDALL_LIKE = "SELECT s.software_id, s.company, s.title, s.os, s.type, s.storage_device, s.notes, s.url  
                                              FROM software s 
                                              LEFT OUTER JOIN tag_inventory ti ON s.software_id = ti.inv
                                              LEFT OUTER JOIN tag t ON ti.tag = t.tag_id
                                              WHERE ";
    protected const SQL_END_FINDALL_LIKE = " GROUP BY s.software_id ORDER BY s.title ASC ";
    protected const MODEL_CLASS = SoftwareModel::class;
    protected const SEARCH_ST_PROC = "search_software";
    protected const ADD_ST_PROC_ID = ":id";
    protected const ADD_ST_PROC = "add_software(" . self::ADD_ST_PROC_ID . ", :company_name, :title, :os_name, :os_version, :type_name, :storage, :notes, :url)";

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

        $vals[':id'] = [$params['software_id'], PDO::PARAM_STR];
        $vals[':company_name'] = [$params['company_name'], PDO::PARAM_STR];
        $vals[':title'] = [$params['title'], PDO::PARAM_STR];
        $vals[':os_name'] = [$params['os_name'], PDO::PARAM_STR];
        $vals[':os_version'] = [$params['os_version'], PDO::PARAM_STR];
        $vals[':type_name'] = [$params['type_name'], PDO::PARAM_STR];
        $vals[':storage'] = [$params['storage_device'], PDO::PARAM_STR];
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
                case 'os':
                    $sql .= " s." . $key . " IN (" . implode(",", array_fill(0, count($value), "?")). ") ";
                    foreach ($value as $x) {
                        
                        $vals[$index++] = [$x, PDO::PARAM_INT];
                    }
                    break;

                case 'tags':
                    $sql .= " t.tag_string LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
                
                default:
                    $sql .= " s." . $key . " LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
            }
        }

        $sql .= static::SQL_END_FINDALL_LIKE;

        return [$sql, $vals];
    }
}