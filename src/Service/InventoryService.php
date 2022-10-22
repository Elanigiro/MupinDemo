<?php
declare(strict_types=1);
namespace App\Service;

use App\Exception\ArrangeTxnException;
use App\Exception\MyFileException;
use App\Model\InventoryModel;
use App\Repository\InventoryRepository;
use App\Service\Abstract\Service;
use App\Utils\MyLogUtils;
use App\Utils\MyPicsUtils;
use PDOException;
use RuntimeException;

use function PHPUnit\Framework\throwException;

class InventoryService extends Service {

    protected InventoryRepository $myRep;
    protected CategoryService $categoryService;

    public function __construct(InventoryRepository $invrep, CategoryService $categoryService) {

        $this->myRep = $invrep;
        $this->categoryService = $categoryService;
    }

    public function tagSearch(string $searchtxt, string $catname): array {

        try {

            $res = $this->myRep->searchProcedure($searchtxt, $catname);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = [];
        }

        return $res;
    }

    public function fetchById(string $id): InventoryModel | false {

        try {

            $res = $this->myRep->findById($id);
        
        } 
        catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = false;
        }

        return $res;
    }

    public function exists(string $id): bool {

        return ($this->fetchById($id) !== false);
    }

    public function fetchCategoryNameById(string $id): string | false {

        try {

            $invItem = $this->myRep->findById($id);

            if ($invItem === false) {

                throw new PDOException("There's no inventory item with the provided id");
            }
        
            $res = $this->categoryService->fetchById($invItem->getCategory()->cat_id)->table_name;

        } catch (PDOException $pdoEx) {

            MyLogUtils::error('TXN', $pdoEx->getMessage(), $pdoEx->getTrace()); 
            $res = false;
        }

        return $res;
    }

    public function deleteItem(string $id, bool $transactional = true): bool {

        try {
            if ($transactional) {

                $this->myRep->beginTransaction();
            }
            
            $this->myRep->deleteItem($id);

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
    public function deleteItemAndPics(string $id, bool $transactional = true): bool {

        try {
            if ($transactional) {

                $this->myRep->beginTransaction();
                if (MyPicsUtils::copyPics($id, MyPicsUtils::IMG_DEFAULT_BASE_DIR, MyPicsUtils::IMG_TMP_DIR, throwing: false) === false) {

                    throw new ArrangeTxnException("Failed to start the transaction. Error during file copying");
                } 
            }

            MyPicsUtils::removePics($id);
            if ($this->deleteItem($id, transactional: false) === false) {

                throw new PDOException("Cannot delete the item, check the provided id");
            }

            if ($transactional) {

                $this->myRep->commitTransaction();
            }

            return true;
        }
        catch (ArrangeTxnException $arrEx) {

            MyLogUtils::error('TXN', $arrEx->getMessage(), $arrEx->getTrace()); 

            $this->myRep->rollbackTransaction();
            return false;
        } 
        catch (PDOException|RuntimeException $transactionEx) {

            MyLogUtils::error('TXN', $transactionEx->getMessage(), $transactionEx->getTrace()); 

            if ($transactional) {

                $this->myRep->rollbackTransaction();
                MyPicsUtils::copyPics($id, MyPicsUtils::IMG_TMP_DIR, MyPicsUtils::IMG_DEFAULT_BASE_DIR, throwing: false);
            }
            return false;
        } 
        finally {

            if ($transactional) {

                MyPicsUtils::clearFolder(MyPicsUtils::IMG_TMP_DIR);
            }
        }
    }

    /** not sure the rollback is solid */
    public function deleteItemPic(string $picDir, bool $transactional = true): bool {

        try {
            if ($transactional) {

                $id = MyPicsUtils::getPicId($picDir);
                if(MyPicsUtils::copyPics($id, MyPicsUtils::IMG_DEFAULT_BASE_DIR, MyPicsUtils::IMG_TMP_DIR, throwing: false) === false) {

                    throw new ArrangeTxnException("Failed to start the transaction. Error during file copying");
                }
            }

            MyPicsUtils::removeSpecificPic($picDir);

            return true;
        } 
        catch (ArrangeTxnException $arrEx) {

            MyLogUtils::error('TXN', $arrEx->getMessage(), $arrEx->getTrace()); 

            return false;
        } 
        catch (RuntimeException $transactionEx) {

            MyLogUtils::error('TXN', $transactionEx->getMessage(), $transactionEx->getTrace()); 
        
            if ($transactional) {

                MyPicsUtils::copyPics($id, MyPicsUtils::IMG_TMP_DIR, MyPicsUtils::IMG_DEFAULT_BASE_DIR, throwing: false);
            } 
            
            return false;
        }
        finally {

            if ($transactional) {

                MyPicsUtils::clearFolder(MyPicsUtils::IMG_TMP_DIR);
            }
        }
    }

    /** not sure the rollback is solid */
    public function addItemPics(string $id, array $pics, int $nextIdx, bool $transactional = true): bool { 


        try {
            if ($transactional) {

                if(MyPicsUtils::copyPics($id, MyPicsUtils::IMG_DEFAULT_BASE_DIR, MyPicsUtils::IMG_TMP_DIR, throwing: false) === false) {

                    throw new ArrangeTxnException("Failed to start the transaction. Error during file copying");
                }
            }

            MyPicsUtils::insertPics($pics, MyPicsUtils::IMG_DEFAULT_BASE_DIR, $id, $nextIdx);
        
            return true;
        }
        catch (ArrangeTxnException $arrEx) {

            MyLogUtils::error('TXN', $arrEx->getMessage(), $arrEx->getTrace()); 

            return false;
        }
        catch (RuntimeException $transactionEx) {

            MyLogUtils::error('TXN', $transactionEx->getMessage(), $transactionEx->getTrace()); 

            if ($transactional) {

                MyPicsUtils::removePics($id, MyPicsUtils::IMG_DEFAULT_BASE_DIR, throwing: false);
                MyPicsUtils::copyPics($id, MyPicsUtils::IMG_TMP_DIR, MyPicsUtils::IMG_DEFAULT_BASE_DIR, throwing: false);
            }

            return false;
        }
        finally {

            if ($transactional) {

                MyPicsUtils::clearFolder(MyPicsUtils::IMG_TMP_DIR);
            }
        }
    }
}