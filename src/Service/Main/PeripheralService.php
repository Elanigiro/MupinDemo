<?php
declare(strict_types=1);
namespace App\Service\Main;

use App\Model\Abstract\MainModel;
use App\Model\Main\PeripheralModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Repository\Main\PeripheralRepository;
use App\Service\Abstract\MainService;
use App\Service\CompanyService;
use App\Service\InventoryService;
use App\Service\PeripheraltypeService;
use App\Service\Virtual\VirtualtagService;
use App\Utils\MyLogUtils;
use App\Utils\MyReflectionUtils;
use PDOException;
use TypeError;

class PeripheralService extends MainService {

    protected const CATEGORY_NAME = "peripheral";

    protected PeripheralRepository $myRep;

    public function __construct(InventoryService $inventoryService, PeripheralRepository $catrep, CompanyService $companyService, PeripheraltypeService $peripheraltypeService, VirtualtagService $virtualtagService) {

        $this->myRep = $catrep;
        $this->serviceArray = ['company' => $companyService, 'type' => $peripheraltypeService, 'tags' => $virtualtagService];
        $this->inventoryService = $inventoryService;
    }

    protected function getMyRep(): MainRepository {

        return $this->myRep;
    }

    protected static function getCatName(): string {

        return self::CATEGORY_NAME;
    }

    /**
     * @param MainModel $peripheral must be a fully validated PeripheralModel object
     * @throws TypeError in case of invalid argument type
    */
    protected static function getParamsToAdd(MainModel $peripheral): array {

        if (!($peripheral instanceof PeripheralModel)) {

            throw new TypeError();
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($peripheral) as $propName) {

            if (isset($peripheral->{$propName})) {

                switch ($propName) {

                    // FK
                    case 'company':
                        $params['company_name'] = $peripheral->getCompany()->company_name;
                        break;

                    // FK
                    case 'type':
                        $params['type_name'] = $peripheral->getPeripheraltype()->type_name;
                        break;

                    default:
                        $params[$propName] = $peripheral->{$propName};
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * @param MainModel $peripheral must be a fully validated PeripheralModel object
    */
    public function addItem(MainModel $peripheral, ?array $uploadedPics): bool {

        if (!($peripheral instanceof PeripheralModel)) {

            return false;
        }

        return $this->addItemCommon($peripheral, $uploadedPics);
    }

    /** 
     * @param MainModel $perip SHOULD be a valid PeripheralModel object
     */
    public function fetchAllLike(MainModel $perip) : array {

        if (!($perip instanceof PeripheralModel)) {

            return [];
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($perip) as $propName) {

            if (isset($perip->{$propName})) {

                switch($propName) {

                    // FKs
                    case 'company':
                    case 'type':
                        $params[$propName] = $this->serviceArray[$propName]->fetchIdsByName($perip->{$propName});
                        break;

                    default:
                        $params[$propName] = $perip->{$propName};
                        break;
                }
            }
        } 

        try {

            $res = $this->myRep->findAllLike($params);
            $this->fetchFKs($res);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = [];
        }

        return $res;
    }
}