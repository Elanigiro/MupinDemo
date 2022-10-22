<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\CategoryModel;
use App\Repository\CategoryRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class CategoryService extends Service {

    protected CategoryRepository $myRep;

    public function __construct(CategoryRepository $catrep) {

        $this->myRep = $catrep;
    }

    public function fetchAllCategories() : array {

        try {

            $res = $this->myRep->findAll();
               
        } 
        catch (PDOException $pdoEx) {
        
            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = [];
        }

        return $res;
    }

    public function fetchById(int $id): CategoryModel | false {

        try {

            $res = $this->myRep->findById($id);
        } catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = false;
        }

        return $res;
    }
}