<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\CategoryModel;
use App\Service\CategoryService;
use App\Utils\MyArrayUtils;
use App\Utils\MyReflectionUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Advanced implements ControllerInterface
{
    protected Engine $plates;
    protected CategoryService $categoryService;

    public const SELECTION_PARAM = "select";

    public function __construct(Engine $plates, CategoryService $categoryService)
    {
        $this->plates = $plates;
        $this->categoryService = $categoryService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $options = $this->categoryService->fetchAllCategories();
        $usrSelection = $request->getQueryParams()[self::SELECTION_PARAM]?? "";

        $usrSelectIdx = MyArrayUtils::array_search_callable($usrSelection, $options, function(CategoryModel $c, String $s) {return ($s === $c->table_name);});


        $usrSelectModel = ($usrSelectIdx !== false)? "\\App\\Model\\Main\\" . ucfirst($options[$usrSelectIdx]->table_name) . "Model" : null;
        $usrSelectProps = (isset($usrSelectModel))? (MyReflectionUtils::getProperties($usrSelectModel)) : [];


        return new Response(
            200,
            [],
            $this->plates->render('advanced', ['user' => MySessionUtils::currentUser(), 'options' => $options, 'selection' => $usrSelectIdx, 'selection_props' => $usrSelectProps, 'selection_model' => $usrSelectModel])
        );
    }
}
