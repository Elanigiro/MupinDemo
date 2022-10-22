<?php
declare(strict_types=1);
namespace App\Service\Virtual;

use App\Model\Virtual\VirtualtagModel;
use App\Repository\Virtual\VirtualtagRepository;
use App\Service\Abstract\VirtualService;
use App\Utils\MyLogUtils;
use PDOException;

class VirtualtagService extends VirtualService {

    protected VirtualtagRepository $myRep;

    public function __construct(VirtualtagRepository $tagrep) {

        $this->myRep = $tagrep;
    }

    public function fetchById(string $id) : VirtualtagModel | false {

        try {

            $res = $this->myRep->findById($id);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = false;
        }

        return $res;
    }
}