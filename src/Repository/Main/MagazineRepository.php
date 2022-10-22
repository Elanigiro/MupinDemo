<?php
declare(strict_types=1);
namespace App\Repository\Main;

use App\Model\Main\MagazineModel;
use App\Repository\Abstract\MainRepository;
use PDO;

class MagazineRepository extends MainRepository {

    public const MAG_NO_RANGE = 10;

    protected const SQL_START_FINDALL_BYIDS = "SELECT * FROM magazine m WHERE m.mag_id ";
    protected const SQL_START_FINDALL_LIKE = "SELECT m.mag_id, m.title, m.mag_no, m.year, m.publisher, m.notes, m.url  
                                              FROM magazine m 
                                              LEFT OUTER JOIN tag_inventory ti ON m.mag_id = ti.inv
                                              LEFT OUTER JOIN tag t ON ti.tag = t.tag_id
                                              WHERE ";
    protected const SQL_END_FINDALL_LIKE = " GROUP BY m.mag_id ORDER BY m.title ASC, m.mag_no ASC ";
    protected const MODEL_CLASS = MagazineModel::class;
    protected const SEARCH_ST_PROC = "search_magazine";
    protected const ADD_ST_PROC_ID = ":id";
    protected const ADD_ST_PROC = "add_magazine(" . self::ADD_ST_PROC_ID . ", :title, :magno, :year_val, :publisher_name, :notes, :url)";

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

        $vals[':id'] = [$params['mag_id'], PDO::PARAM_STR];
        $vals[':title'] = [$params['title'], PDO::PARAM_STR];
        $vals[':magno'] = [$params['mag_no'], PDO::PARAM_INT];
        $vals[':year_val'] = [$params['year'], PDO::PARAM_STR];
        $vals[':publisher_name'] = [$params['publisher_name'], PDO::PARAM_STR];
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

                case 'publisher':
                    $sql .= " m." . $key . " IN (" . implode(",", array_fill(0, count($value), "?")). ") ";
                    foreach ($value as $x) {
                        
                        $vals[$index++] = [$x, PDO::PARAM_INT];
                    }
                    break;

                case 'year':
                    $century = intdiv($value, 100);
                    $decade = intdiv(($value % 100), 10);
                    $min = ($century * 100) + ($decade * 10);
                    $max = $min + 9;
                    $sql .= " m.year BETWEEN ? AND ? ";
                    $vals[$index++] = [$min, PDO::PARAM_STR];
                    $vals[$index++] = [$max, PDO::PARAM_STR];
                    break;
                
                case 'mag_no':
                    $min = $value - static::MAG_NO_RANGE;
                    $max = $value + static::MAG_NO_RANGE;
                    $sql .= " m.mag_no BETWEEN ? AND ? ";
                    $vals[$index++] = [$min, PDO::PARAM_INT];
                    $vals[$index++] = [$max, PDO::PARAM_INT];                   
                    break;

                case 'tags':
                    $sql .= " t.tag_string LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
                
                default:
                    $sql .= " m." . $key . " LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
            }
        }

        $sql .= static::SQL_END_FINDALL_LIKE;

        return [$sql, $vals];
    }
}