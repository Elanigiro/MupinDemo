<?php
declare(strict_types=1);
namespace App\Repository\Main;

use App\Model\Main\BookModel;
use App\Repository\Abstract\MainRepository;
use PDO;

class BookRepository extends MainRepository {

    public const PAGE_RANGE = 50;

    protected const SQL_START_FINDALL_BYIDS = "SELECT * FROM book b WHERE b.book_id ";
    protected const SQL_START_FINDALL_LIKE = "SELECT b.book_id, b.title, b.author, b.publisher, b.year, b.pages, b.isbn, b.notes, b.url
                                              FROM book b 
                                              LEFT OUTER JOIN tag_inventory ti ON b.book_id = ti.inv
                                              LEFT OUTER JOIN tag t ON ti.tag = t.tag_id
                                              WHERE ";
    protected const SQL_END_FINDALL_LIKE = " GROUP BY b.book_id ORDER BY b.title ASC ";
    protected const MODEL_CLASS = BookModel::class;
    protected const SEARCH_ST_PROC = "search_book";
    protected const ADD_ST_PROC_ID = ":id";
    protected const ADD_ST_PROC = "add_book(" . self::ADD_ST_PROC_ID . ", :title, :author_firstname, :author_lastname, :publisher_name, :year_val, :pages, :isbn, :notes, :url)";

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

        $vals[':id'] = [$params['book_id'], PDO::PARAM_STR];
        $vals[':title'] = [$params['title'], PDO::PARAM_STR];
        $vals[':author_firstname'] = [$params['author_firstname'], PDO::PARAM_STR];
        $vals[':author_lastname'] = [$params['author_lastname'], PDO::PARAM_STR];
        $vals[':publisher_name'] = [$params['publisher_name'], PDO::PARAM_STR];
        $vals[':year_val'] = [$params['year'], PDO::PARAM_STR];
        $vals[':pages'] = (isset($params['pages']))? [$params['pages'], PDO::PARAM_INT] : [null, PDO::PARAM_NULL];
        $vals[':isbn'] = (isset($params['isbn'])) ? [$params['isbn'], PDO::PARAM_STR] : [null, PDO::PARAM_NULL];
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

                case 'author':
                case 'publisher':
                    $sql .= " b." . $key . " IN (" . implode(",", array_fill(0, count($value), "?")). ") ";
                    foreach ($value as $x) {
                        
                        $vals[$index++] = [$x, PDO::PARAM_INT];
                    }
                    break;

                case 'year':
                    $century = intdiv($value, 100);
                    $decade = intdiv(($value % 100), 10);
                    $min = ($century * 100) + ($decade * 10);
                    $max = $min + 9;
                    $sql .= " b.year BETWEEN ? AND ? ";
                    $vals[$index++] = [$min, PDO::PARAM_STR];
                    $vals[$index++] = [$max, PDO::PARAM_STR];
                    break;
                
                case 'pages':
                    $min = $value - static::PAGE_RANGE;
                    $max = $value + static::PAGE_RANGE;
                    $sql .= " b.pages BETWEEN ? AND ? ";
                    $vals[$index++] = [$min, PDO::PARAM_INT];
                    $vals[$index++] = [$max, PDO::PARAM_INT];                   
                    break;

                case 'tags':
                    $sql .= " t.tag_string LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
                
                default:
                    $sql .= " b." . $key . " LIKE CONCAT('%', ?, '%') ";
                    $vals[$index++] = [$value, PDO::PARAM_STR];
                    break;
            }
        }

        $sql .= static::SQL_END_FINDALL_LIKE;

        return [$sql, $vals];
    }
}