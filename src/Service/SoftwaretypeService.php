<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\SoftwaretypeModel;
use App\Repository\SoftwaretypeRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class SoftwaretypeService extends Service {

    protected SoftwaretypeRepository $myRep;

    public function __construct(SoftwaretypeRepository $softrep) {

        $this->myRep = $softrep;
    }

    public function fetchById(int $id) : SoftwaretypeModel | false {

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
        foreach ($tmpRes as $softtype) {

            $res[] = $softtype->type_id;
        }

        return $res;
    }
}