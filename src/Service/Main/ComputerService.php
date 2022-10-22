<?php
declare(strict_types=1);
namespace App\Service\Main;

use App\Model\Abstract\MainModel;
use App\Model\Main\ComputerModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Repository\Main\ComputerRepository;
use App\Service\Abstract\MainService;
use App\Service\CompanyService;
use App\Service\CpuService;
use App\Service\InventoryService;
use App\Service\OsService;
use App\Service\Virtual\VirtualtagService;
use App\Utils\MyLogUtils;
use App\Utils\MyReflectionUtils;
use PDOException;
use TypeError;

class ComputerService extends MainService {

    protected const CATEGORY_NAME = "computer";

    protected ComputerRepository $myRep;

    public function __construct(InventoryService $inventoryService, ComputerRepository $catrep, CompanyService $companyService, CpuService $cpuService, OsService $osService, VirtualtagService $virtualtagService) {

        $this->myRep = $catrep;
        $this->serviceArray = ['company' => $companyService, 'cpu' => $cpuService, 'os' => $osService, 'tags' => $virtualtagService];
        $this->inventoryService = $inventoryService;
    }

    protected function getMyRep(): MainRepository {

        return $this->myRep;
    }

    protected static function getCatName(): string {

        return self::CATEGORY_NAME;
    }

    /**
     * @param MainModel $computer must be a fully validated ComputerModel object
     * @throws TypeError in case of invalid argument type
    */
    protected static function getParamsToAdd(MainModel $computer): array {

        if (!($computer instanceof ComputerModel)) {

            throw new TypeError();
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($computer) as $propName) {

            if (isset($computer->{$propName})) {

                switch ($propName) {

                    // FK
                    case 'company':
                        $params['company_name'] = $computer->getCompany()->company_name;
                        break;

                    // FK
                    case 'cpu':
                        $params['cpu_name'] = $computer->getCpu()->cpu_name;
                        break;

                    // FK
                    case 'os':
                        $params['os_name'] = $computer->getOs()->os_name;
                        $params['os_version'] = $computer->getOs()->os_version;
                        break;

                    default:
                        $params[$propName] = $computer->{$propName};
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * @param MainModel $computer must be a fully validated ComputerModel object
    */
    public function addItem(MainModel $computer, ?array $uploadedPics): bool {

        if (!($computer instanceof ComputerModel)) {

            return false;
        }

        return $this->addItemCommon($computer, $uploadedPics);
    }

    /** 
     * @param MainModel $pc SHOULD be a valid ComputerModel object
     */
    public function fetchAllLike(MainModel $pc) : array {

        if (!($pc instanceof ComputerModel)) {

            return [];
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($pc) as $propName) {

            if (isset($pc->{$propName})) {

                switch($propName) {

                    // FKs
                    case 'company':
                    case 'cpu':
                    case 'os':
                        $params[$propName] = $this->serviceArray[$propName]->fetchIdsByName($pc->{$propName});
                        break;

                    default:
                        $params[$propName] = $pc->{$propName};
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