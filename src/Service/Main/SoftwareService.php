<?php
declare(strict_types=1);
namespace App\Service\Main;

use App\Model\Abstract\MainModel;
use App\Model\Main\SoftwareModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Repository\Main\SoftwareRepository;
use App\Service\Abstract\MainService;
use App\Service\CompanyService;
use App\Service\InventoryService;
use App\Service\OsService;
use App\Service\SoftwaretypeService;
use App\Service\Virtual\VirtualtagService;
use App\Utils\MyLogUtils;
use App\Utils\MyReflectionUtils;
use PDOException;
use TypeError;

class SoftwareService extends MainService {

    protected const CATEGORY_NAME = "software";

    protected SoftwareRepository $myRep;

    public function __construct(InventoryService $inventoryService, SoftwareRepository $catrep, CompanyService $companyService, OsService $osService, SoftwaretypeService $softwaretypeService, VirtualtagService $virtualtagService) {

        $this->myRep = $catrep;
        $this->serviceArray = ['company' => $companyService, 'os' => $osService, 'type' => $softwaretypeService, 'tags' => $virtualtagService];
        $this->inventoryService = $inventoryService;
    }

    protected function getMyRep(): MainRepository {

        return $this->myRep;
    }

    protected static function getCatName(): string {

        return self::CATEGORY_NAME;
    }

    /**
     * @param MainModel $software must be a fully validated SoftwareModel object
     * @throws TypeError in case of invalid argument type
    */
    protected static function getParamsToAdd(MainModel $software): array {

        if (!($software instanceof SoftwareModel)) {

            throw new TypeError();
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($software) as $propName) {

            if (isset($software->{$propName})) {

                switch ($propName) {

                    // FK
                    case 'company':
                        $params['company_name'] = $software->getCompany()->company_name;
                        break;

                    // FK
                    case 'os':
                        $params['os_name'] = $software->getOs()->os_name;
                        $params['os_version'] = $software->getOs()->os_version;
                        break;

                    // FK
                    case 'type':
                        $params['type_name'] = $software->getSoftwaretype()->type_name;
                        break;

                    default:
                        $params[$propName] = $software->{$propName};
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * @param MainModel $software must be a fully validated SoftwareModel object
    */
    public function addItem(MainModel $software, ?array $uploadedPics): bool {

        if (!($software instanceof SoftwareModel)) {

            return false;
        }

        return $this->addItemCommon($software, $uploadedPics);
    }

    /** 
     * @param MainModel $software SHOULD be a valid SoftwareModel object
     */
    public function fetchAllLike(MainModel $software) : array {

        if (!($software instanceof SoftwareModel)) {

            return [];
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($software) as $propName) {

            if (isset($software->{$propName})) {

                switch($propName) {

                    // FKs
                    case 'company':
                    case 'os':
                    case 'type':
                        $params[$propName] = $this->serviceArray[$propName]->fetchIdsByName($software->{$propName});
                        break;

                    default:
                        $params[$propName] = $software->{$propName};
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