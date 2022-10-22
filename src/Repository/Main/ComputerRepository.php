<?php
declare(strict_types=1);
namespace App\Repository\Main;

use App\Model\Main\ComputerModel;
use App\Repository\Abstract\MainRepository;
use PDO;

class ComputerRepository extends MainRepository {

    public const CLK_HZ_BYTE_RANGE = '100000000';

    protected const SQL_START_FINDALL_BYIDS = "SELECT * FROM computer c WHERE c.computer_id ";
    protected const SQL_START_FINDALL_LIKE = "SELECT c.computer_id, c.company, c.model, c.year, c.cpu, c.clock_hz, c.ram_byte, c.storage_byte, c.os, c.notes, c.url  
                                              FROM computer c 
                                              LEFT OUTER JOIN tag_inventory ti ON c.computer_id = ti.inv
                                              LEFT OUTER JOIN tag t ON ti.tag = t.tag_id
                                              WHERE ";
    protected const SQL_END_FINDALL_LIKE = " GROUP BY c.computer_id ORDER BY c.model ASC ";
    protected const MODEL_CLASS = ComputerModel::class;
    protected const SEARCH_ST_PROC = "search_computer";
    protected const ADD_ST_PROC_ID = ":id";
    protected const ADD_ST_PROC = "add_computer(" . self::ADD_ST_PROC_ID . ", :company_name, :model, :year_val, :cpu_name, :hz, :ram, :storage, :os_name, :os_version, :notes, :url)";

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

        $vals[':id'] = [$params['computer_id'], PDO::PARAM_STR];
        $vals[':company_name'] = [$params['company_name'], PDO::PARAM_STR];
        $vals[':model'] = [$params['model'], PDO::PARAM_STR];
        $vals[':year_val'] = [$params['year'], PDO::PARAM_STR];
        $vals[':cpu_name'] = [$params['cpu_name'], PDO::PARAM_STR];
        $vals[':hz'] = [$params['clock_hz'], PDO::PARAM_STR];
        $vals[':ram'] = [$params['ram_byte'], PDO::PARAM_STR];
        $vals[':storage'] = (isset($params['storage_byte'])) ? [$params['storage_byte'], PDO::PARAM_STR] : [null, PDO::PARAM_NULL];
        $vals[':os_name'] = (isset($params['os_name'])) ? [$params['os_name'], PDO::PARAM_STR] : [null, PDO::PARAM_NULL];
        $vals[':os_version'] = (isset($params['os_version'])) ? [$params['os_version'], PDO::PARAM_STR] : [null, PDO::PARAM_NULL];
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
                case 'cpu':
                case 'os':
                    $sql .= " c." . $key . " IN (" . implode(",", array_fill(0, count($value), "?")). ") ";
                    foreach ($value as $x) {
                        
                        $vals[$index++] = [$x, PDO::PARAM_INT];
                    }
                    break;

                case 'year':
                    $century = intdiv($value, 100);
                    $decade = intdiv(($value % 100), 10);
                    $min = ($century * 100) + ($decade * 10);
                    $max = $min + 9;
                    $sql .= " c.year BETWEEN ? AND ? ";
                    $vals[$index++] = [$min, PDO::PARAM_STR];
                    $vals[$index++] = [$max, PDO::PARAM_STR];
                    break;
                
                case 'clock_hz':
                case 'ram_byte':
                case 'storage_byte':
                    $min = bcsub($value, static::CLK_HZ_BYTE_RANGE);
                    $max = bcadd($value, static::CLK_HZ_BYTE_RANGE);
                    // lexicographical ordering with leading zeros
                    $sql .= " c." . $key . " BETWEEN LPAD(?, 50, '0') AND LPAD(?, 50, '0') ";
                    $vals[$index++] = [$min, PDO::PARAM_STR];
                    $vals[$index++] = [$max, PDO::PARAM_STR];                    
                    break;

                case 'tags':
                    $sql .= " t.tag_string LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
                
                default:
                    $sql .= " c." . $key . " LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
            }
        }

        $sql .= static::SQL_END_FINDALL_LIKE;

        return [$sql, $vals];
    }
}