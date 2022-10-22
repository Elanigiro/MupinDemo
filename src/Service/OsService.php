<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\OsModel;
use App\Repository\OsRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class OsService extends Service {

    protected OsRepository $myRep;

    public function __construct(OsRepository $catrep) {

        $this->myRep = $catrep;
    }

    public function fetchById(int $id) : OsModel | false {

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
        foreach ($tmpRes as $os) {

            $res[] = $os->os_id;
        }

        return $res;
    }
}