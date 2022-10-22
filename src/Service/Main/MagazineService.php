<?php
declare(strict_types=1);
namespace App\Service\Main;

use App\Model\Abstract\MainModel;
use App\Model\Main\MagazineModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Repository\Main\MagazineRepository;
use App\Service\Abstract\MainService;
use App\Service\InventoryService;
use App\Service\PublisherService;
use App\Service\Virtual\VirtualtagService;
use App\Utils\MyLogUtils;
use App\Utils\MyReflectionUtils;
use PDOException;
use TypeError;

class MagazineService extends MainService {

    protected const CATEGORY_NAME = "magazine";

    protected MagazineRepository $myRep;

    public function __construct(InventoryService $inventoryService, MagazineRepository $catrep, PublisherService $publisherService, VirtualtagService $virtualtagService) {

        $this->myRep = $catrep;
        $this->serviceArray = ['publisher' => $publisherService, 'tags' => $virtualtagService];
        $this->inventoryService = $inventoryService;
    }

    protected function getMyRep(): MainRepository {

        return $this->myRep;
    }

    protected static function getCatName(): string {

        return self::CATEGORY_NAME;
    }

    /**
     * @param MainModel $magazine must be a fully validated MagazineModel object
     * @throws TypeError in case of invalid argument type
    */
    protected static function getParamsToAdd(MainModel $magazine): array {

        if (!($magazine instanceof MagazineModel)) {

            throw new TypeError();
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($magazine) as $propName) {

            if (isset($magazine->{$propName})) {

                switch ($propName) {

                    // FK
                    case 'publisher':
                        $params['publisher_name'] = $magazine->getPublisher()->pub_name;
                        break;

                    default:
                        $params[$propName] = $magazine->{$propName};
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * @param MainModel $magazine must be a fully validated MagazineModel object
    */
    public function addItem(MainModel $magazine, ?array $uploadedPics): bool {

        if (!($magazine instanceof MagazineModel)) {

            return false;
        }

        return $this->addItemCommon($magazine, $uploadedPics);
    }

    /** 
     * @param MainModel $magazine SHOULD be a valid MagazineModel object
     */
    public function fetchAllLike(MainModel $magazine) : array {

        if (!($magazine instanceof MagazineModel)) {

            return [];
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($magazine) as $propName) {

            if (isset($magazine->{$propName})) {

                switch($propName) {

                    // FK
                    case 'publisher':
                        $params[$propName] = $this->serviceArray[$propName]->fetchIdsByName($magazine->{$propName});
                        break;

                    default:
                        $params[$propName] = $magazine->{$propName};
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