<?php
declare(strict_types=1);

namespace SimpleMVC\Test\Controller;

use App\Controller\Login;
use App\Exception\UnauthorizedException;
use App\Model\UserModel;
use App\Service\UserService;
use App\Utils\MyLogUtils;
use App\Utils\MySessionUtils;
use League\Plates\Engine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginTest extends TestCase
{
   
    /** @var ServerRequestInterface|MockObject */
    private $request;

    /** @var ResponseInterface|MockObject */
    private $response;

    /** @var UserService|MockObject */
    private $userService;

    private Login $login;

    private Engine $plates;


    public function setUp(): void
    {
        $this->plates = new Engine(__DIR__ . '/../../src/View');
        $this->userService = $this->createMock(UserService::class);
        $this->login = new Login($this->plates, $this->userService);
        $this->request = $this->createMock(ServerRequestInterface::class);      
        $this->response = $this->createMock(ResponseInterface::class);
    }

    public function tearDown(): void {

        if (MySessionUtils::currentUser() !== null) {

            MySessionUtils::logout();
        }        

        if (is_file(MyLogUtils::DIRECTORY)) {

            unlink(MyLogUtils::DIRECTORY);
        }
    }

    public function testLoginReturn200(): void
    {
        $this->request->method('getMethod')->willReturn('GET');

        $response = $this->login->execute($this->request, $this->response);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginReturn303(): void {
        $this->request->method('getParsedBody')->willReturn(['email' => 'm@n.com', 'pwd' => 'password']);
        $this->request->method('getMethod')->willReturn('POST');
        $this->userService->method('checkUserPassword')->willReturn(UserModel::__set_state($this->request->getParsedBody()));

        $response = $this->login->execute($this->request, $this->response);

        $this->assertEquals(303, $response->getStatusCode());
    }

    public function testLoginReturn400(): void {

        $this->request->method('getParsedBody')->willReturn(['email' => 'm@n.com', 'pwd' => 'password']);
        $this->request->method('getMethod')->willReturn('POST');
        $this->userService->method('checkUserPassword')->willThrowException(new UnauthorizedException());

        $response = $this->login->execute($this->request, $this->response);

        $this->assertEquals(400, $response->getStatusCode());
    }

}