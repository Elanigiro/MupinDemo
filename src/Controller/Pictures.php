<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\MyPicsUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Pictures implements ControllerInterface
{
    protected Engine $plates;

    public const ITEM_PARAM = "item";
    public const ADD_DEL_RESULT_PARAM = "result";

    public function __construct(Engine $plates)
    {
        $this->plates = $plates;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $pictures = [];

        $itemId = ($request->getQueryParams()[self::ITEM_PARAM])?? null;
        if ($itemId) {

            $pictures = MyPicsUtils::findPicsById($itemId, sorted: true);
        }
        $picDeleteMsg = ($request->getQueryParams()[self::ADD_DEL_RESULT_PARAM]) ?? null; 

        return new Response(
            200,
            [],
            $this->plates->render('pictures', ['user' => MySessionUtils::currentUser(), 'itemId' => $itemId, 'pics' => $pictures, 'msg' => $picDeleteMsg])
        );
    }
}
