<?php

declare(strict_types=1);

namespace App\Controller;

use App\Alias\MainServicesAlias;
use App\Exception\InvalidArrayException;
use App\Exception\ValidationException;
use App\Model\Abstract\MainModel;
use App\Model\CategoryModel;
use App\Service\CategoryService;
use App\Service\InventoryService;
use App\Utils\MyArrayUtils;
use App\Utils\MyLogUtils;
use App\Utils\MyPicsUtils;
use App\Utils\MyReflectionUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class AddItem implements ControllerInterface
{
    protected Engine $plates;
    protected MainServicesAlias $mainServices;
    protected CategoryService $categoryService;
    protected InventoryService $inventoryService;

    public const SELECTION_PARAM = "select";
    public const FILE_PARAM_HTML = "pictures[]";
    public const FILE_PARAM = "pictures";

    public function __construct(Engine $plates, MainServicesAlias $mainServices, CategoryService $categoryService, InventoryService $inventoryService)
    {
        $this->plates = $plates;
        $this->mainServices = $mainServices;
        $this->categoryService = $categoryService;
        $this->inventoryService = $inventoryService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        if (MySessionUtils::currentUser() === null) {

            MyLogUtils::warning('MUPIN_DB', "Attempted access by unauthorized user", ['location' => __CLASS__]);

            return new Response(
                303,
                ["location" => "/"]
            );
        }

        $options = $this->categoryService->fetchAllCategories();
        $usrSelection = $request->getQueryParams()[self::SELECTION_PARAM] ?? ($request->getParsedBody()[self::SELECTION_PARAM] ?? "");

        $usrSelectIdx = MyArrayUtils::array_search_callable($usrSelection, $options, function (CategoryModel $c, String $s) {
            return ($s === $c->table_name);
        });


        $usrSelectModel = ($usrSelectIdx !== false) ? "\\App\\Model\\Main\\" . ucfirst($options[$usrSelectIdx]->table_name) . "Model" : null;
        $usrSelectProps = (isset($usrSelectModel)) ? (MyReflectionUtils::getProperties($usrSelectModel)) : [];

        switch ($request->getMethod()) {

            case 'GET':
                return new Response(
                    200,
                    [],
                    $this->plates->render('additem', ['user' => MySessionUtils::currentUser(), 'options' => $options, 'selection' => $usrSelectIdx, 'selection_props' => $usrSelectProps, 'selection_model' => $usrSelectModel])
                );
                break;

            case 'POST':
                // I will pass this variable by reference and update it accordingly
                $uploadedFiles = $request->getUploadedFiles();
                $modelToAdd = $this->validPost($request->getParsedBody(), $uploadedFiles, $usrSelectModel);
                if ($modelToAdd === false) {

                    MyLogUtils::info('MUPIN_DB', "Failed validation of new item", ['location' => __CLASS__, 'user' => MySessionUtils::currentUser()]);

                    return new Response(
                        400,
                        [],
                        $this->plates->render('additem', ['user' => MySessionUtils::currentUser(), 'options' => $options, 'selection' => $usrSelectIdx, 'selection_props' => $usrSelectProps, 'selection_model' => $usrSelectModel, 'msg' => 'invalid'])
                    );
                }

                $transactionStatus = $this->mainServices[$usrSelection]->addItem($modelToAdd, ($uploadedFiles[self::FILE_PARAM]) ?? null);
    
                $dbInsertMsg = ($transactionStatus)? 'success' : 'failure';
                $responseStatus = ($transactionStatus) ? 201 : 500;

                MyLogUtils::info('MUPIN_DB', $dbInsertMsg, ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'item_id' => $modelToAdd->getId()]);

                return new Response(
                    $responseStatus,
                    [],
                    $this->plates->render('additem', ['user' => MySessionUtils::currentUser(), 'options' => $options, 'selection' => $usrSelectIdx, 'selection_props' => $usrSelectProps, 'selection_model' => $usrSelectModel, 'msg' => $dbInsertMsg])
                );                
                break;
        }
    }

    /** create a new unique id
     * @note conflicts should be rare
     */
    protected function uniqueId(): string {

        do {
            $res = MainModel::idGen();

        } while($this->inventoryService->fetchById($res) !== false);

        return $res;
    }

    /** @param array &$fileParams is set to null after verifying the user did not send any files */
    protected function validPost(array $postParams, array &$fileParams, ?string $modelClass): MainModel | false {

        if (!isset($modelClass)) {

            return false;
        }

        // NOTE: Both conditions should be true if the user has indeed sent a file
        if (isset($fileParams[self::FILE_PARAM][0]) && (!empty($fileParams[self::FILE_PARAM][0]->getClientMediaType()))) {

            if (count($fileParams[self::FILE_PARAM]) > MyPicsUtils::IMG_LIMIT) {

                return false;
            }
            
            foreach ($fileParams[self::FILE_PARAM] as $pic) {

                if ($pic->getError() !== UPLOAD_ERR_OK) {

                    return false;
                }

                if (MyPicsUtils::validPicExtension($pic) === false) {

                    return false;
                }
            }
        }
        else {

            // no file was sent
            $fileParams = null;
        }

        //new model with validation
        try {
     
            $modelFactory = $modelClass . "::factoryFromArray";
            unset($postParams[self::SELECTION_PARAM]);
            // generate a unique id for the item
            $postParams[call_user_func([$modelClass, 'getIdName'])] = self::uniqueId();
            // true stands for thorough validations
            $model = $modelFactory($postParams, thorough: true);
        } catch (ValidationException $vex) {
            
            return false;
        }

        return $model;
    }
}
