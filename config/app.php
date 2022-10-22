<?php
/**
 * Skeleton application for SimpleMVC
 * 
 * @link      http://github.com/simplemvc/skeleton
 * @copyright Copyright (c) Enrico Zimuel (https://www.zimuel.it)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

use App\Utils\MySessionUtils;
use Psr\Container\ContainerInterface;
use SimpleMVC\Controller\Error404;
use SimpleMVC\Controller\Error405;

return [
    'routing' => [
        'routes' => require 'route.php'
    ],
    'error' => [
        '404' => Error404::class,
        '405' => Error405::class
    ],
    'bootstrap' => function(ContainerInterface $c) {

        // PHP log set-up
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('error_log', __DIR__ . '/../data/logs/php/error.log');

        // Timezone set-up
        date_default_timezone_set('UTC');

        // Session set-up
        MySessionUtils::start();
    }
];