<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Home implements ControllerInterface
{
    protected Engine $plates;
    protected CategoryService $categoryService;

    public function __construct(Engine $plates, CategoryService $categoryService)
    {
        $this->plates = $plates;
        $this->categoryService = $categoryService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {     

        $options = $this->categoryService->fetchAllCategories();

        return new Response(
            200,
            [],
            $this->plates->render('home', ['user' => MySessionUtils::currentUser(), 'options' => $options, 'selection' => false])
        );
    }
}
