<?php
declare(strict_types=1);
namespace App\Service;

use App\Repository\AuthorRepository;
use App\Model\AuthorModel;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class AuthorService extends Service {

    protected AuthorRepository $myRep;

    public function __construct(AuthorRepository $autrep) {

        $this->myRep = $autrep;
    }

    public function fetchById(int $id) : AuthorModel | false {

        try {

            $res = $this->myRep->findById($id);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace());        
            $res = false;
        }

        return $res;
    }

    public function fetchIdsByName(string $searchName) : array {

        try {

            $tmpRes = $this->myRep->findByNameLike($searchName);
          
        } 
        catch (PDOException $pdoEx) {
        
            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $tmpRes = [];
        }
        
        $res = [];
        foreach ($tmpRes as $aut) {

            $res[] = $aut->aut_id;
        }

        return $res;
    }
}