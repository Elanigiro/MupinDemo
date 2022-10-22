<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\CompanyModel;
use App\Repository\CompanyRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class CompanyService extends Service {

    protected CompanyRepository $myRep;

    public function __construct(CompanyRepository $catrep) {

        $this->myRep = $catrep;
    }

    public function fetchById(int $id) : CompanyModel | false {

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
        foreach ($tmpRes as $company) {

            $res[] = $company->company_id;
        }

        return $res;
    }
}