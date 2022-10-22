<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use App\Model\UserModel;
use App\Service\UserService;
use App\Utils\MyLogUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleMVC\Controller\ControllerInterface;

class Login implements ControllerInterface
{
    protected Engine $plates;
    protected UserService $userService;

    public function __construct(Engine $plates, UserService $userService)
    {
        $this->plates = $plates;
        $this->userService = $userService;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (MySessionUtils::currentUser() !== null) {

            return new Response(
                303,
                ["location" => "/"]
            );
        }

        switch($request->getMethod()) {

            case 'GET':
                return new Response(
                    200,
                    [],
                    $this->plates->render('login', ['status' => "none"])
                );
                break;

            case 'POST':
                try {

                    $reqUser = UserModel::factoryFromArray($request->getParsedBody(), thorough: false, hashed: false);

                    $this->userService->checkUserPassword($reqUser);

                    // login successful
                    MySessionUtils::login($reqUser->email);
                    MyLogUtils::info('USER_DB', "Login successful", ['user' => $reqUser->email, 'client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown']);

                    return new Response(
                        303, // 303 - transforms the POST => GET
                        ['location' => '/']
                    );
                }
                catch (ValidationException | UnauthorizedException $ex) {

                    MyLogUtils::warning('USER_DB', $ex->getMessage(), array_merge(['client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown', $ex->getTrace()]));

                    return new Response(
                        400,
                        [],
                        $this->plates->render('login', ['status' => "failure"])
                    );
                }   
                break;
        }
    }
}
