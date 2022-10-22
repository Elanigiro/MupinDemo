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

class EditItem implements ControllerInterface {
    protected Engine $plates;
    protected MainServicesAlias $mainServices;
    protected InventoryService $inventoryService;

    public const ITEM_PARAM = "item";
    public const MODEL_PARAM = "model_class";

    public function __construct(Engine $plates, MainServicesAlias $mainServices, InventoryService $inventoryService) {
        $this->plates = $plates;
        $this->mainServices = $mainServices;
        $this->inventoryService = $inventoryService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {

        if (MySessionUtils::currentUser() === null) {

            MyLogUtils::warning('MUPIN_DB', "Attempted access by unauthorized user", ['location' => __CLASS__]);

            return new Response(
                303,
                ["location" => "/"]
            );
        }

        switch ($request->getMethod()) {

            case 'GET':
                $modelToEdit = $this->validGet($request->getQueryParams());
                if ($modelToEdit === false) {

                    return new Response(
                        303,
                        ["location" => "/"]
                    );
                }
                return new Response(
                    200,
                    [],
                    $this->plates->render('edititem', ['user' => MySessionUtils::currentUser(), 'item' => $modelToEdit])
                );
                break;

            case 'POST':
                $editedModel = $this->validPost($request->getParsedBody());
                if ($editedModel === false) {

                    MyLogUtils::info('MUPIN_DB', "Failed validation of edited item", ['location' => __CLASS__, 'user' => MySessionUtils::currentUser()]);

                    return new Response(
                        400,
                        [],
                        $this->plates->render('edititem', ['user' => MySessionUtils::currentUser(), 'msg' => 'invalid'])
                    );
                }

                $transactionStatus = $this->mainServices[MyReflectionUtils::getShortModelName($editedModel)]->updateItem($editedModel, transactional: true);

                $dbInsertMsg = ($transactionStatus) ? 'success' : 'failure';
                $responseStatus = ($transactionStatus) ? 201 : 500;

                MyLogUtils::info('MUPIN_DB', $dbInsertMsg, ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'item_id' => $editedModel->getId()]);

                return new Response(
                    $responseStatus,
                    [],
                    $this->plates->render('edititem', ['user' => MySessionUtils::currentUser(), 'msg' => $dbInsertMsg])
                );
                break;
        }
    }

    protected function validGet(array $getParams): MainModel | false {

        $itemId = ($getParams[self::ITEM_PARAM]) ?? null;

        if (!isset($itemId)) {

            return false;
        }

        $category = $this->inventoryService->fetchCategoryNameById($itemId);

        // item not found in inventory
        if ($category === false) {

            return false;
        }

        return $this->mainServices[$category]->fetchById($itemId);     
    }

    protected function validPost(array $postParams): MainModel | false {

        $modelClass = $postParams[self::MODEL_PARAM] ?? null;

        if (!isset($modelClass) || (!class_exists($modelClass)) || (!is_a($modelClass, MainModel::class, allow_string: true))) {

            return false;
        }

        $modelId = $postParams[call_user_func([$modelClass, 'getIdName'])] ?? null;

        if (!isset($modelId) || !($this->mainServices[MyReflectionUtils::getShortModelName($modelClass)]->exists($modelId))) {

            return false;
        }

        //new model with validation
        try {

            $modelFactory = $modelClass . "::factoryFromArray";
            unset($postParams[self::MODEL_PARAM]);
            // true stands for thorough validations
            $model = $modelFactory($postParams, thorough: true);
        } catch (ValidationException $vex) {

            return false;
        }

        return $model;
    }
}
