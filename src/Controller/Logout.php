<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\MyLogUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Logout implements ControllerInterface
{
    protected Engine $plates;

    public function __construct(Engine $plates)
    {
        $this->plates = $plates;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        $currentUser = MySessionUtils::logout();

        if ($currentUser !== null) {

            MyLogUtils::info('USER_DB', "Logout", ['user' => $currentUser, 'client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown']);
        }

        return new Response(
            303,
            ['location' => '/']
        );
    }
}
