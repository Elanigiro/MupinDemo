<?php

declare(strict_types=1);

namespace App\Controller;

use App\Alias\MainServicesAlias;
use App\Exception\InvalidArrayException;
use App\Model\Abstract\MainModel;
use App\Service\InventoryService;
use App\Utils\MySearchUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Search implements ControllerInterface
{
    protected Engine $plates;
    protected MainServicesAlias $mainServices;
    protected InventoryService $inventoryService;

    public const DEFAULT_GET = "all";
    public const DEFAULT_POST = "";
    public const CAT_PARAM = "html_category";
    public const SEARCH_PARAM = "search_txt";
    public const SEARCH_PARAM_MAX_LEN = 255;

    public function __construct(Engine $plates, MainServicesAlias $mainServices, InventoryService $inventoryService)
    {
        $this->plates = $plates;
        $this->mainServices = $mainServices;
        $this->inventoryService = $inventoryService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        switch($request->getMethod()) {

            case 'GET':
                $requestParamsArray = $this->validGet($request->getQueryParams());
                if ($requestParamsArray === false) {

                    return new Response(
                        303,
                        ['location' => '/']
                    );
                }

                // check whether to search in all categories or just one
                if ($requestParamsArray[self::CAT_PARAM] === self::DEFAULT_GET) {

                    $res = [];
                    foreach ($this->mainServices as $currentService) {
                                                     
                        $res = array_merge($res, $currentService->standardSearch($requestParamsArray[self::SEARCH_PARAM]));
                    }
                }
                else {

                    $res = $this->mainServices[$requestParamsArray[self::CAT_PARAM]]->standardSearch($requestParamsArray[self::SEARCH_PARAM]);
                }
                // sort by relevance DESC
                usort($res, function($obj1, $obj2) {return ($obj2->relevance <=> $obj1->relevance);});
                // render response
                return new Response(
                    200,
                    [],
                    $this->plates->render('search', ['user' => MySessionUtils::currentUser(), 'origin' => '', 'results' => $res])
                );
                break;

            case 'POST':
                $requestBodyArray = $request->getParsedBody();
                $modelToFind = $this->validPost($requestBodyArray);
                if ($modelToFind === false) {

                    // invalid request/model -> no item found
                    return new Response(
                        400,
                        [],
                        $this->plates->render('search', ['user' => MySessionUtils::currentUser(), 'origin' => 'advanced', 'results' => []])
                    );
                }

                $tmpRes = $this->mainServices[$requestBodyArray[self::CAT_PARAM]]->fetchAllLike($modelToFind);

                return new Response(
                    200,
                    [],
                    $this->plates->render('search', ['user' => MySessionUtils::currentUser(), 'origin' => 'advanced', 'results' => $tmpRes])
                );

                break;
        }
    }

    protected function validGet(array $params) : array | false {

        if ((!isset($params[self::CAT_PARAM])) || (!isset($params[self::SEARCH_PARAM]))) {

            return false;
        }
        $params[self::SEARCH_PARAM] = MySearchUtils::processString($params[self::SEARCH_PARAM]);
        $len = strlen($params[self::SEARCH_PARAM]);
        if (($len === 0) || ($len > self::SEARCH_PARAM_MAX_LEN)) {

            return false;
        }
        if (!$this->validCategory($params[self::CAT_PARAM], self::DEFAULT_GET)) {

            return false;
        }
        return $params;
    }

    protected function validPost(array $params) : MainModel | false {

        if ((!isset($params[self::CAT_PARAM]))) {

            return false;
        }
        if (!$this->validCategory($params[self::CAT_PARAM], self::DEFAULT_POST)) {

            return false;
        }
        
        try {
            $modelFactory = "\\App\\Model\\Main\\" . ucfirst($params[self::CAT_PARAM]) . "Model::factoryFromArray";
            unset($params[self::CAT_PARAM]);
            $model = $modelFactory($params);
        }
        catch (InvalidArrayException $vex) {

            return false;
        }

        return $model;
    }

    protected function validCategory(string $cat, string $default) : bool {

        if (($cat !== $default) && (!isset($this->mainServices[$cat]))) {

            return false;
        }
        return true;
    }
}
