<?php
/**
 * Skeleton application for SimpleMVC
 * 
 * @link      http://github.com/simplemvc/skeleton
 * @copyright Copyright (c) Enrico Zimuel (https://www.zimuel.it)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

use App\Alias\MainServicesAlias;
use App\Alias\MyPDODataAlias;
use App\Alias\MyPDOLoginAlias;
use App\Service\CategoryService;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;

return [
    'view_path' => 'src/View',
    Engine::class => function(ContainerInterface $c) {
        return new Engine($c->get('view_path'));
    },
    MyPDODataAlias::class => function(ContainerInterface $c) {

        $db_mupin = $c->get('db_mupin');
        return new MyPDODataAlias($db_mupin['dsn'], $db_mupin['user'], $db_mupin['pwd']);
    },   
    MyPDOLoginAlias::class => function(ContainerInterface $c) {

        $db_mupin_login = $c->get('db_mupin_login');
        return new MyPDOLoginAlias($db_mupin_login['dsn'], $db_mupin_login['user'], $db_mupin_login['pwd']);
    },  
    MainServicesAlias::class => function (ContainerInterface $c) {

        $categoryService = $c->get(CategoryService::class);
        $categories = $categoryService->fetchAllCategories();

        $mainServices = [];
        foreach ($categories as $category) {

            $mainServices[$category->table_name] = $c->get("\\App\\Service\\Main\\" . ucfirst($category->table_name) . "Service");
        }

        return new MainServicesAlias($mainServices);
    },
    'db_mupin' => [
        'dsn' => 'mysql:dbname=mupin;host=db',
        'user' => 'mupin',
        'pwd' => 'mupinisall'
    ],
    'db_mupin_login' => [
        'dsn' => 'mysql:dbname=mupin_login;host=db',
        'user' => 'mupin_login',
        'pwd' => 'mupinloginisall'
    ]
];
