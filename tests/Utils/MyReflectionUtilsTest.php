<?php
declare(strict_types=1);

namespace SimpleMVC\Test\Utils;

use App\Utils\MyReflectionUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class MyTestComputerModel {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
     */
    public string $computer_id;

    /** @fk GET company_name SET company_id
     * @pattern ~^(?=.*\S)(.+)$~
     */
    public string $company;
    /** @pattern ~^(?=.*\S)(.+)$~ */
    public string $model;
    /** @valid int min 1901 max 2100 */
    public int $year;
    /** @fk GET cpu_name SET cpu_id
     *  @pattern ~^(?=.*\S)(.+)$~
     */
    public string $cpu;
    /** @valid bcnum min 1 max inf */
    public string $clock_hz;
    /** @valid bcnum min 1 max inf */
    public string $ram_byte;
    /** @valid bcnum min 1 max inf */
    public ?string $storage_byte = null;
    /** @fk GET os_name,os_version SET os_id
     *  @pattern ~^[ \t]*(.+\b)[ \t]*,[ \t]*(.+\b)[ \t]*$~
     */
    public ?string $os = null;
    /** @valid textarea
     *  @pattern ~^[\s\S]*$~
     */
    public ?string $notes = null;

    public ?string $url = null;
    /** @fk GET tag_list SET null 
     *  @pattern ~^(?=.*\w.*)(?!.*,?[ '\w\d]{51,})([ ,'\w\d]+)$~
     */
    public ?string $tags = null;
}

class MyTestBookModel {}
class MyTestPeripheralModel {}
class MyTestSoftwareModel {}
class MyTestMagazineModel {}



final class MyReflectionUtilsTest extends TestCase
{

    public function getPropertiesInput(): array {

        return [
            [MyTestComputerModel::class, null, (new ReflectionClass(MyTestComputerModel::class))->getProperties(null)],
            [new MyTestComputerModel(), null, (new ReflectionClass(MyTestComputerModel::class))->getProperties(null)],
            ['', null, []]
        ];
    }

    /**
     * @dataProvider getPropertiesInput
     */
    public function testGetProperties(object|string $objectOrClass, int|null $filter, array $expected): void
    {

        $result = MyReflectionUtils::getProperties($objectOrClass, $filter);

        $this->assertSame(count($expected), count($result));
        for ($i=0; $i < count($expected); $i++) { 
            $this->assertEquals($expected[$i], $result[$i]);
        }
    }
    
    public function getPropertiesNameInput(): array {

        return [
            [MyTestComputerModel::class, null, ['computer_id', 'company', 'model', 'year', 'cpu', 'clock_hz', 'ram_byte', 'storage_byte', 'os', 'notes', 'url', 'tags']],
            [new MyTestComputerModel(), null, ['computer_id', 'company', 'model', 'year', 'cpu', 'clock_hz', 'ram_byte', 'storage_byte', 'os', 'notes', 'url', 'tags']],
            ['', null, []]
        ];
    }

    /**
     * @dataProvider getPropertiesNameInput
     */
    public function testGetPropertiesName(object|string $objectOrClass, int|null $filter, array $expected): void
    {

        $result = MyReflectionUtils::getPropertiesName($objectOrClass, $filter);

        $this->assertContainsOnly('string', $result);
        // order does not matter
        sort($expected);
        sort($result);
        $this->assertSame($expected, $result);
    }

    public function getShortModelNameInput(): array {

        return [
            [MyTestComputerModel::class, 'mytestcomputer'],
            [MyTestBookModel::class, 'mytestbook'],
            [MyTestMagazineModel::class, 'mytestmagazine'],
            [MyTestPeripheralModel::class, 'mytestperipheral'],
            [MyTestSoftwareModel::class, 'mytestsoftware']
        ];
    }

    /**
     * @dataProvider getShortModelNameInput
     */
    public function testGetShortModelName(object|string $objectOrClass, string $expected): void
    {

        $result = MyReflectionUtils::getShortModelName($objectOrClass);

        $this->assertSame($expected, $result);
    }

    public function getShortModelNameExceptionInput(): array {

        return [
            ['Mario'],
            ['Luigi'],
            ['Toad']
        ];
    }

    /**
     * @dataProvider getShortModelNameExceptionInput
     */
    public function testGetShortModelNameException(object|string $objectOrClass): void
    {

        $this->expectException(ReflectionException::class);

        MyReflectionUtils::getShortModelName($objectOrClass);
    }

    public function getPropertyTypeInput(): array {

        return [
            [MyTestComputerModel::class, 'mario', 'null'],
            [MyTestComputerModel::class, 'computer_id', 'string'],
            ['MyTestComputerModel::class', 'computer_id', 'null']
        ];
    }

    /**
     * @dataProvider getPropertyTypeInput
     */
    public function testGetPropertyType(object|string $objectOrClass, string $property, string $expected): void {

        $result = MyReflectionUtils::getPropertyType($objectOrClass, $property);

        $this->assertSame($expected, $result);
    }
    
    public function getRegexPropertyNamesInput(): array {

        return [
            [MyTestComputerModel::class, '/^.*omp.*$/', null, ['computer_id', 'company']],
            [MyTestComputerModel::class, '/^.*ompute.*$/', null, ['computer_id']],
            [MyTestComputerModel::class, '/^.*omputee.*$/', null, []],
            ['MyTestComputerModel::class', '/computer_id/', null, []]
        ];
    }

    /**
     * @dataProvider getRegexPropertyNamesInput
     */
    public function testGetRegexPropertyNames(object|string $objectOrClass, string $pattern, int|null $filter, array $expected): void {

        $result = MyReflectionUtils::regexPropertyNames($objectOrClass, $pattern, $filter);

        $this->assertContainsOnly('string', $result);
        // order does not matter
        sort($expected);
        sort($result);
        $this->assertSame($expected, $result);
    }

    public function getPropertyDocInput(): array {

        return [
            [MyTestComputerModel::class, 'clock_hz', "/** @valid bcnum min 1 max inf */"],
            [MyTestComputerModel::class, 'url', ""]
        ];
    }

    /**
     * @dataProvider getPropertyDocInput
     */
    public function testGetPropertyDoc(object|string $objectOrClass, string $property, string $expected): void {

        $result = MyReflectionUtils::getPropertyDoc($objectOrClass, $property);
  
        $this->assertSame($expected, $result);
    }

    public function getValidationInfoInput(): array {

        return [
            [MyTestComputerModel::class, 'clock_hz', ['bcnum','1', 'inf', '^(.*)$']],
            [MyTestComputerModel::class, 'url', ['string', '0', 'inf', '^(.*)$']],
            [MyTestComputerModel::class, 'os', ['string', '0', 'inf', '^[ \t]*(.+\b)[ \t]*,[ \t]*(.+\b)[ \t]*$']]
        ];
    }

    /**
     * @dataProvider getValidationInfoInput
     */
    public function testGetValidationInfo(object|string $objectOrClass, string $property, array $expected): void {

        $result = MyReflectionUtils::getValidationInfo($objectOrClass, $property);
  
        $this->assertSame($expected, $result);
    }

    public function isValidPropInput(): array {

        return [
            [new MyTestComputerModel(), 'clock_hz', true, '100000000000000000000', true],
            [new MyTestComputerModel(), 'clock_hz', false, '0000', true],
            [new MyTestComputerModel(), 'clock_hz', true, '0000', false],
            [new MyTestComputerModel(), 'tags', false, '=dsaf:?, dahjsfghadsgfhadsghjfgasdhfghadsgfhdsgafhjdsgfhjgahsjgfjhsdgfhsadsadfdsf', true],
            [new MyTestComputerModel(), 'tags', true, '=dsaf:?, dahjsfghadsgfhadsghjfgasdhfghadsgfhdsgafhjdsgfhjgahsjgfjhsdgfhsadsadfdsf', false],
            [new MyTestComputerModel(), 'tags', true, 'dsaf, dahjsfghdsg656fhjgahsjgfj, hsdgfHsads\'adfdsf', true]
        ];
    }

    /**
     * @dataProvider isValidPropInput
     */
    public function testIsValidProp(object $obj, string $property, bool $thorough, mixed $value, bool $expected): void {

        $obj->{$property} = $value;

        $result = MyReflectionUtils::isValidProp($obj, $property, $thorough);
  
        $this->assertSame($expected, $result);
    }
}