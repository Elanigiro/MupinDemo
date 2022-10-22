<?php
declare(strict_types=1);
namespace App\Service\Abstract;

use App\Model\Abstract\MainModel;
use App\Model\InventoryModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Service\InventoryService;
use App\Utils\MyLogUtils;
use App\Utils\MyPicsUtils;
use App\Utils\MySearchUtils;
use PDOException;
use RuntimeException;

abstract class MainService extends Service {

    abstract protected function getMyRep(): MainRepository;
    abstract protected static function getCatName(): string;
    abstract public function addItem(MainModel $item, ?array $uploadedPics) : bool;
    abstract protected static function getParamsToAdd(MainModel $book): array;
    abstract public function fetchAllLike(MainModel $subject) : array;

    protected InventoryService $inventoryService;

    public function standardSearch(string $searchtxt): array {

        try {

            $res = $this->getMyRep()->searchProcedure($searchtxt);
            $this->fetchFKs($res);

            $tagRes = $this->inventoryService->tagSearch($searchtxt, $this->getCatName());

            // This line updates $res by incrementing the tag relevance for the item AND updates $tagRes by removing duplicates
            $tagRes = MySearchUtils::processItemsAndTags($res, $tagRes);

            // I fetch all the remaining items in $tagRes
            $additionalRes = $this->fetchAllByIds($tagRes);
            //Note: In theory (count($additionalRes) <= count($tagRes))
            MySearchUtils::applyRelevanceToItems($additionalRes, $tagRes);

            $res = array_merge($res, $additionalRes);
        }
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = [];
        }

        return $res;
    }

    protected function deleteItem(string $id, bool $transactional = true): bool {

        return $this->inventoryService->deleteItem($id, $transactional);
    }

    /** @param MainModel $newItem MUST be a thoroughly validated item with an id already in inventory */
    public function updateItem(MainModel $newItem, bool $transactional = true): bool {

        try {
            if ($transactional) {

                $this->myRep->beginTransaction();
            }

            if ($this->deleteItem($newItem->getId(), transactional: false) === false) {

                throw new PDOException("Could not update the item, check the provided id");
            }
            if ($this->addItemCommon($newItem, null, transactional: false) === false) {

                throw new PDOException("Error while updating the item");
            }

            if ($transactional) {

                $this->myRep->commitTransaction();
            }
            
            return true;
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
        
            if ($transactional) {

                $this->myRep->rollbackTransaction();    
            }
            return false;
        }
    }

    /** not sure the picture rollback is solid */
    protected function addItemCommon(MainModel $item, ?array $uploadedPics, bool $transactional = true): bool {

        try {

            if ($transactional) {

                $this->getMyRep()->beginTransaction();
            }            

            if (isset($uploadedPics)) {

                MyPicsUtils::insertPics($uploadedPics, MyPicsUtils::IMG_DEFAULT_BASE_DIR, $item->getId(), startIdx: 0);
            }

            $params = $this->getParamsToAdd($item);
            $tags = VirtualtagModel::parseTags($params['tags'] ?? '');

            // prevents (case insensitive) duplicate tags
            $lowered = array_map('strtolower', $tags);
            $tags = array_intersect_key($tags, array_unique($lowered));

            unset($params['tags']);

            $this->getMyRep()->addProcedure($params, $tags);

            if ($transactional) {

                $this->getMyRep()->commitTransaction();
            }            

            return true;
        } catch (PDOException|RuntimeException $transactionEx) {

            MyLogUtils::error('TXN', $transactionEx->getMessage(), $transactionEx->getTrace()); 
            
            if ($transactional) {

                if (isset($uploadedPics)) {

                    MyPicsUtils::removePics($item->getId(), MyPicsUtils::IMG_DEFAULT_BASE_DIR, throwing: false);
                }

                $this->getMyRep()->rollbackTransaction();
            }            

            return false;
        }
    }

    public function fetchById(string $id, bool $shallow = false): MainModel | false {

        try {

            // find objects by ids
            $res = $this->getMyRep()->findById($id);
            if (($res !== false) && (!$shallow)) {

                $res->refresh($this->serviceArray);
            }
        } catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = false;
        }

        return $res;
    }

    public function exists(string $id): bool {

        return ($this->fetchById($id, true) !== false);
    }

    /** 
     * @param array $inventoryItems MUST be an array of valid InventoryModel objects
     */
    public function fetchAllByIds(array $inventoryItems) : array {

        if (empty($inventoryItems)) {

            return [];
        }

        $params = [];
        foreach ($inventoryItems as $currentItem) {
            
            $params[] = $currentItem->inv_id;
        }

        try {

            // find objects by ids
            $res = $this->getMyRep()->findAllByIds($params);
            $this->fetchFKs($res);

        } catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = [];
        }

        return $res;
    }
}