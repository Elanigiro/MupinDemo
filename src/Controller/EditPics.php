<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\InventoryService;
use App\Utils\MyLogUtils;
use App\Utils\MyPicsUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class EditPics implements ControllerInterface
{
    protected Engine $plates;
    protected InventoryService $inventoryService;

    public const DEL_PARAM = "del";
    public const ITEM_PARAM = "item";
    public const ADD_PARAM_HTML = "add[]";
    public const ADD_PARAM = "add";

    public function __construct(Engine $plates, InventoryService $inventoryService)
    {
        $this->plates = $plates;
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

        switch ($request->getMethod()) {

            // delete picture
            case 'GET':
                $picDir = $this->validGet($request->getQueryParams());
                if ($picDir !== false) {

                    $itemId = MyPicsUtils::getPicId($picDir);
                    $transactionStatus = $this->inventoryService->deleteItemPic($picDir, transactional: true);
                    $resultMsg = ($transactionStatus) ? 'success' : 'failure';
                }
                else {

                    $itemId = "none";
                    $resultMsg = 'invalid';
                }                             
                break;

            // add picture(s)
            case 'POST':
                $nextIdx = $this->validPost($request->getParsedBody(), $request->getUploadedFiles());
                if ($nextIdx !== false) {

                    $itemId = $request->getParsedBody()[self::ITEM_PARAM];
                    $pics = $request->getUploadedFiles()[self::ADD_PARAM];
                    $transactionStatus = $this->inventoryService->addItemPics($itemId, $pics, $nextIdx, transactional: true);
                    $resultMsg = ($transactionStatus) ? 'success' : 'failure';
                } else {

                    $itemId = $request->getParsedBody()[self::ITEM_PARAM]?? 'none';
                    $resultMsg = 'invalid';
                }
                break;
        }

        MyLogUtils::info('MUPIN_DB', $resultMsg . (($request->getMethod() === 'POST')? ' add' : ' delete'), ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'item_id' => $itemId]);

        return new Response(
            303,
            ["location" => "/pictures?" . Pictures::ITEM_PARAM . "=$itemId&" . Pictures::ADD_DEL_RESULT_PARAM . "=$resultMsg"]
        );
    }

    protected function validGet(array $getParams): string | false {

        $fileName = ($getParams[self::DEL_PARAM]) ?? null;
        if (!isset($fileName)) {

            return false;
        }

        $tmpDir = MyPicsUtils::IMG_DEFAULT_BASE_DIR . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($tmpDir)) {

            return false;
        }

        return $tmpDir;
    }

    /** @return int|false either the first free index for the item pics or false */
    protected function validPost(array $postParams, array $fileParams): int | false {

        $itemId = $postParams[self::ITEM_PARAM];
        if (!isset($itemId)) {

            return false;
        }

        if (!($this->inventoryService->exists($itemId))) {

            return false;
        }

        // NOTE: Both conditions should be true if the user has indeed sent a file
        if (isset($fileParams[self::ADD_PARAM][0]) && (!empty($fileParams[self::ADD_PARAM][0]->getClientMediaType()))) {

            $nextIdx = MyPicsUtils::getNextIdx($itemId, MyPicsUtils::IMG_DEFAULT_BASE_DIR);

            if ((count($fileParams[self::ADD_PARAM]) + $nextIdx) > MyPicsUtils::IMG_LIMIT) {

                return false;
            }

            foreach ($fileParams[self::ADD_PARAM] as $pic) {

                if ($pic->getError() !== UPLOAD_ERR_OK) {

                    return false;
                }

                if (MyPicsUtils::validPicExtension($pic) === false) {

                    return false;
                }
            }
        } else {

            // no file was sent
            return false;
        }

        return $nextIdx;
    }
}
