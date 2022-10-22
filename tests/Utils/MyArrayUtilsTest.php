<?php
declare(strict_types=1);

namespace SimpleMVC\Test\Utils;

use App\Model\Main\BookModel;
use App\Utils\MyArrayUtils;
use PHPUnit\Framework\TestCase;

final class MyArrayUtilsTest extends TestCase
{

    public function getDataSearchCallable(): array {

        return [
            ['needle', ['mario', 'Paolo', 'Needle', 'gino', 'needle', 'marcello', 'needle'], fn($x,$y) => (strtolower($x) === strtolower($y)), 2],
            ['needle', ['mario', 'Paolo', 'Needle', 'gino', 'needle', 'marcello', 'needle'], fn($x,$y) => ($x === $y), 4]
        ];
    }

    /**
     * @dataProvider getDataSearchCallable
     */
    public function testSearchCallable(mixed $needle, array $haystack, callable $foo, int|false $expected): void
    {

        $result = MyArrayUtils::array_search_callable($needle, $haystack, $foo);

        // assertSame uses strict comparison (aka identity)
        $this->assertSame($expected, $result);
    }

    public function getDataArrayWalk(): array {

        return [
            [['mario' => 'UNO', 'Paolo' => 'DUE', 'Needle' => 'TRE', 'gino' => 'QUATTRO', 'needle' => 'CINQUE'], fn ($k, $v) => ([strtoupper($k), strtolower($v)]), ['MARIO' => 'uno', 'PAOLO' => 'due', 'NEEDLE' => 'cinque', 'GINO' => 'quattro']]
        ];
    }

    /**
     * @dataProvider getDataArrayWalk
     */
    public function testArrayWalk(array $origin, callable $foo, array $expected): void {

        $result = MyArrayUtils::array_walk_processed_copy($origin, $foo);

        // assertSame uses strict comparison (aka identity)
        $this->assertSame($expected, $result);
    }

    public function getDataArrayFromField(): array {

        return [
            [[BookModel::__set_state(['book_id' => '1', 'title' => '2', 'year' => 3333]), BookModel::__set_state(['book_id' => 'a', 'title' => 'b', 'year' => 4444]), BookModel::__set_state(['book_id' => 'A', 'title' => 'B', 'year' => 5555])], 'year', [3333, 4444, 5555]]
        ];
    }

    /**
     * @dataProvider getDataArrayFromField
     */
    public function testArrayFromField(array $a, string $fieldName, array $expected): void {

        $result = MyArrayUtils::getArrayFromField($a, $fieldName);

        // assertSame uses strict comparison (aka identity)
        $this->assertSame($expected, $result);
    }

    public function getDataPregFirst(): array {

        return [
            [['aaa', 2222, 3333, 'aaaa'], '/^\\d+$/', 2222]
        ];
    }

    /**
     * @dataProvider getDataPregFirst
     */
    public function testPregFirst(array $a, string $pattern, mixed $expected): void {

        $result = MyArrayUtils::preg_first($a, $pattern);

        // assertSame uses strict comparison (aka identity)
        $this->assertSame($expected, $result);
    }
}