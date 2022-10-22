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

class DeleteItem implements ControllerInterface {
    protected Engine $plates;
    protected MainServicesAlias $mainServices;
    protected InventoryService $inventoryService;

    public const ITEM_PARAM = "item";

    public function __construct(Engine $plates, InventoryService $inventoryService) {
        $this->plates = $plates;
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

        $itemId = $this->validGet($request->getQueryParams());

        if ($itemId === false) {

            MyLogUtils::info('MUPIN_DB', "Invalid request", ['location' => __CLASS__, 'user' => MySessionUtils::currentUser()]);

            return new Response(
                400,
                [],
                $this->plates->render('edititem', ['user' => MySessionUtils::currentUser(), 'msg' => 'invalid'])
            );
        }

        $transactionStatus = $this->inventoryService->deleteItemAndPics($itemId, transactional: true);

        $dbInsertMsg = ($transactionStatus) ? 'success' : 'failure';
        $responseStatus = ($transactionStatus) ? 200 : 500;

        MyLogUtils::info('MUPIN_DB', $dbInsertMsg, ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'item_id' => $itemId]);

        return new Response(
            $responseStatus,
            [],
            $this->plates->render('edititem', ['user' => MySessionUtils::currentUser(), 'msg' => $dbInsertMsg])
        );
    }

    protected function validGet(array $getParams): string | false {

        $itemId = ($getParams[self::ITEM_PARAM]) ?? null;

        if (!isset($itemId)) {

            return false;
        }

        return ($this->inventoryService->fetchById($itemId))? $itemId : false;
    }
}
