<?php
declare(strict_types=1);

namespace SimpleMVC\Test\Utils;

use App\Utils\MyPasswordUtils;
use PHPUnit\Framework\TestCase;

final class MyPasswordUtilsTest extends TestCase
{

    public function getDataInput(): array {

        return [
            ['', false],
            ['Marc3&', false],
            ['&&&&&&&&&&&&&&&&&&&', false],
            ['1111111111111111111', false],
            ['aaaaaaaaaaaaaaaaaaa', false],
            ['AAAAAAAAAAAAAAAAAAA', false],
            ['Marcello', false],
            ['Marcello1', false],
            ['marcello1&', false],
            ['marcello&', false],
            ['marcello1', false],
            ['marcello1}', false],
            ['mArr%c3^l\lodshjgsfghjhfsgjhasfjghfsjghjdfshgjhfsgjhfsjghfkjshgjdfshgjfshgjhdfsjgkhdfjsghdfjshgkjdfshg', false],
            ['Marce llo1&', false],
            [' Marcello1&', false],
            ['Marcello1& ', false],
            [' Mar cello1& ', false],
            ['Marcello1&', true],
            ['mArcello1&', true],
            ['mArrc3llo&', true],
            ['mArrc3l&lo', true],
            ['mArrc3l/lo', true],
            ['mArrc3l\lo', true],
            ['mArr%c3^l\lo', true]
        ];
    }

    /**
     * @dataProvider getDataInput
     */
    public function testValidPwd(string $pwd, bool $expected): void
    {

        $result = MyPasswordUtils::validPwd($pwd);

        // assertSame uses strict comparison (aka identity)
        $this->assertSame($expected, $result);
    }
}