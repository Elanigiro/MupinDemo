<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\PeripheraltypeModel;
use App\Repository\PeripheraltypeRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class PeripheraltypeService extends Service {

    protected PeripheraltypeRepository $myRep;

    public function __construct(PeripheraltypeRepository $perrep) {

        $this->myRep = $perrep;
    }

    public function fetchById(int $id) : PeripheraltypeModel | false {

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
        foreach ($tmpRes as $pertype) {

            $res[] = $pertype->type_id;
        }

        return $res;
    }

}