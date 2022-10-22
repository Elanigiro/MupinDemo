<?php
declare(strict_types=1);

namespace SimpleMVC\Test\Model;

use App\Exception\InvalidArrayException;
use App\Model\UserModel;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{

    public function factoryFromArrayInput(): array {

        return [
            [['email' => 'a@b.com', 'pwd' => 'aA1!567890'], true, true, 'valid'],
            [['email' => 'a@b.com', 'pwd' => 'aA1567890'], true, true, 'invalid'],
            [['email' => 'a@b.com', 'pwd' => 'aA1567890'], false, true, 'valid']
        ];
    }

    /**
     * @dataProvider factoryFromArrayInput
     */
    public function testFactoryFromArray(array $input, bool $thorough, bool $hashed, string $expectedResult): void
    {

        if ($expectedResult === 'invalid') {

            $this->expectException(InvalidArrayException::class);
        }
        else {

            $this->expectNotToPerformAssertions();
        }

        UserModel::factoryFromArray($input, $thorough, $hashed);
    }

    public function factoryFromArraySupplementInput(): array {

        return [
            [['email' => 'a@b.com', 'pwd' => 'aA1!567890'], true, true],
            [['email' => 'a@b.com', 'pwd' => 'aA1567890'], false, true],
            [['email' => 'a@b.com', 'pwd' => 'aA1567890'], false, false],
            [['email' => 'a@b.com', 'pwd' => 'aA1!567890'], true, false]
        ];
    }

    /**
     * @dataProvider factoryFromArraySupplementInput
     */
    public function testFactoryFromArraySupplement(array $input, bool $thorough, bool $hashed): void
    {

        $result = UserModel::factoryFromArray($input, $thorough, $hashed);

        if ($hashed) {

            $this->assertTrue(password_verify($input['pwd'], $result->pwd));
        }
        else {

            $this->assertSame($input['pwd'], $result->pwd);
        }
    }

    public function testHashed(): void {

        $usr = UserModel::factoryFromArray(['email' => 'a@b.com', 'pwd' => 'aA1!567890'], true, false);

        $usr->hashed();

        $this->assertNotEquals('aA1!567890', $usr->pwd);        
    }    
}