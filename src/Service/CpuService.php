<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\CpuModel;
use App\Repository\CpuRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class CpuService extends Service {

    protected CpuRepository $myRep;

    public function __construct(CpuRepository $catrep) {

        $this->myRep = $catrep;
    }

    public function fetchById(int $id) : CpuModel | false {

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
        foreach ($tmpRes as $cpu) {

            $res[] = $cpu->cpu_id;
        }

        return $res;
    }
}