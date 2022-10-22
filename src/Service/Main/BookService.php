<?php
declare(strict_types=1);
namespace App\Service\Main;

use App\Model\Abstract\MainModel;
use App\Model\Main\BookModel;
use App\Model\Virtual\VirtualtagModel;
use App\Repository\Abstract\MainRepository;
use App\Repository\Main\BookRepository;
use App\Service\Abstract\MainService;
use App\Service\AuthorService;
use App\Service\InventoryService;
use App\Service\PublisherService;
use App\Service\Virtual\VirtualtagService;
use App\Utils\MyLogUtils;
use App\Utils\MyReflectionUtils;
use PDOException;
use TypeError;

class BookService extends MainService {

    protected const CATEGORY_NAME = "book";

    protected BookRepository $myRep;

    public function __construct(InventoryService $inventoryService, BookRepository $catrep, AuthorService $authorService, PublisherService $publisherService, VirtualtagService $virtualtagService) {

        $this->myRep = $catrep;
        $this->serviceArray = ['author' => $authorService, 'publisher' => $publisherService, 'tags' => $virtualtagService];
        $this->inventoryService = $inventoryService;
    }

    protected function getMyRep(): MainRepository {

        return $this->myRep;
    }

    protected static function getCatName(): string {

        return self::CATEGORY_NAME;
    }

    /**
     * @param MainModel $book must be a fully validated BookModel object
     * @throws TypeError in case of invalid argument type
    */
    protected static function getParamsToAdd(MainModel $book): array {

        if (!($book instanceof BookModel)) {

            throw new TypeError();
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($book) as $propName) {

            if (isset($book->{$propName})) {

                switch ($propName) {

                    // FK
                    case 'author':
                        $params['author_firstname'] = $book->getAuthor()->first_name;
                        $params['author_lastname'] = $book->getAuthor()->last_name;
                        break;
                    // FK
                    case 'publisher':
                        $params['publisher_name'] = $book->getPublisher()->pub_name;
                        break;

                    default:
                        $params[$propName] = $book->{$propName};
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * @param MainModel $book must be a fully validated BookModel object
    */
    public function addItem(MainModel $book, ?array $uploadedPics): bool {

        if (!($book instanceof BookModel)) {

            return false;
        }

        return $this->addItemCommon($book, $uploadedPics);
    }

    /** 
     * @param MainModel $book SHOULD be a valid BookModel object
     */
    public function fetchAllLike(MainModel $book) : array {

        if (!($book instanceof BookModel)) {

            return [];
        }

        $params = [];

        foreach (MyReflectionUtils::getPropertiesName($book) as $propName) {

            if (isset($book->{$propName})) {

                switch($propName) {

                    // FKs
                    case 'author':
                    case 'publisher':
                        $params[$propName] = $this->serviceArray[$propName]->fetchIdsByName($book->{$propName});
                        break;

                    default:
                        $params[$propName] = $book->{$propName};
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