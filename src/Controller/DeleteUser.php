<?php

declare(strict_types=1);

namespace App\Controller;

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

class DeleteUser implements ControllerInterface
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

        if (MySessionUtils::currentUser() === null) {

            MyLogUtils::warning('USER_DB', "Attempted access by unauthorized user", ['location' => __CLASS__, 'client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown']);

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
                    $this->plates->render('deleteuser', ['user' => MySessionUtils::currentUser(), 'status' => "none"])
                );
                break;

            case 'POST':
                try {

                    $reqUser = UserModel::factoryFromArray($request->getParsedBody(), thorough: false, hashed: false);
                    if ($this->userService->deleteUser($reqUser) === false) {throw new ValidationException("Cannot delete the user!");}

                    MyLogUtils::info('USER_DB', "User \"$reqUser->email\" successfully deleted", ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown']);

                    // if I deleted the current user => logout
                    if ($reqUser->email === MySessionUtils::currentUser()) {
                        
                        return new Response(
                            303,
                            ['location' => '/logout']
                        );
                    }

                    return new Response(
                        200,
                        [],
                        $this->plates->render('deleteuser', ['user' => MySessionUtils::currentUser(), 'status' => "success_delete"])
                    );
                }
                catch (ValidationException $ex) {

                    MyLogUtils::info('USER_DB', $ex->getMessage(), ['location' => __CLASS__, 'user' => MySessionUtils::currentUser(), 'client' => ($_SERVER['REMOTE_ADDR'])?? 'unknown']);

                    return new Response(
                        400,
                        [],
                        $this->plates->render('deleteuser', ['user' => MySessionUtils::currentUser(), 'status' => "failure", 'status_msg' => $ex->getMessage()])
                    );
                }  
                break;
        }
    }
}
