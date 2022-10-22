<?php
declare(strict_types=1);
namespace App\Service;

use App\Model\PublisherModel;
use App\Repository\PublisherRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use PDOException;

class PublisherService extends Service {

    protected PublisherRepository $myRep;

    public function __construct(PublisherRepository $catrep) {

        $this->myRep = $catrep;
    }

    public function fetchById(int $id) : PublisherModel | false {

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
        foreach ($tmpRes as $publisher) {

            $res[] = $publisher->pub_id;
        }

        return $res;
    }

}