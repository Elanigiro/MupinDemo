<?php
declare(strict_types=1);
namespace App\Repository\Utils;

use PDO;
use PDOStatement;

class DBManager {

    private PDO $myPdo;

    public function __construct(PDO $pdo) {

        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->myPdo = $pdo;
    }

    /**
     * @throws PDOException
     * @param string $query insert, delete, update
     */
    public function executeUpdate(string $query, array $bindValues) : int {

        $stmt = $this->myPdo->prepare($query);
        foreach ($bindValues as $key => $values) {
            
            $stmt->bindValue($key, $values[0], $values[1]);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @throws PDOException
     * @param string $query select
     */
    public function executeQuery(string $query, array $bindValues) : PDOStatement {

        $stmt = $this->myPdo->prepare($query);
        foreach ($bindValues as $key => $values) {
            
            $stmt->bindValue($key, $values[0], $values[1]);
        }
        $stmt->execute();

        return $stmt;
    }

    public function beginTransaction() {

        $this->myPdo->beginTransaction();     
    }

    public function commit() {

        $this->myPdo->commit();
    }

    public function rollback() {

        $this->myPdo->rollback();
    }

    /**
     * @throws PDOException if the fetch method encounters an error
     * @throws TypeError if the result set is not compatible with the method requested
     */
    public static function fetchAllClass(PDOStatement $results, string $className, string $methodName): array {

        $res = [];

        if (method_exists($className, $methodName)) {

            $record = $results->fetch(PDO::FETCH_ASSOC);
            while ($record !== false) {

                $res[] = call_user_func([$className, $methodName], $record);             

                // update
                $record = $results->fetch(PDO::FETCH_ASSOC);
            }
        }

        return $res;
    }

    /**
     * It returns an associative array [pk => obj]
     * @throws PDOException if the fetch method encounters an error
     * @throws TypeError if the result set is not compatible with the method requested
     * @param string $keyMethodName is a non-static method that takes no arguments and returns a key (int|string)
     * @warning $keyMethodName must return a valid key for an associative array
     */
    public static function fetchAllClassAsMap(PDOStatement $results, string $className, string $factoryMethodName, string $keyMethodName): array {

        $res = [];

        if (method_exists($className, $factoryMethodName) && (method_exists($className, $keyMethodName))) {

            $record = $results->fetch(PDO::FETCH_ASSOC);
            while ($record !== false) {

                $tmpObj = call_user_func([$className, $factoryMethodName], $record);
                $tmpKey = call_user_func([$tmpObj, $keyMethodName]);
                $res[$tmpKey] = $tmpObj;           

                // update
                $record = $results->fetch(PDO::FETCH_ASSOC);
            }
        }

        return $res;
    }

    /**
     * @throws PDOException if the fetch method encounters an error
     * @throws TypeError if the result set is not compatible with the method requested
     * @return object|null the object of the requested type or null if the method requested does not exist or no record found
     */
    public static function fetchClass(PDOStatement $results, string $className, string $methodName): object | null {

        if (method_exists($className, $methodName)) {

            $record = $results->fetch(PDO::FETCH_ASSOC);
            if ($record !== false) {

                return call_user_func([$className, $methodName], $record);      
            }
        }

        return null;
    }
}