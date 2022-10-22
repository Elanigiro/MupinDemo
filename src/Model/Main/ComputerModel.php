<?php
declare(strict_types=1);
namespace App\Model\Main;

use App\Model\Abstract\MainModel;
use App\Model\CompanyModel;
use App\Model\CpuModel;
use App\Model\OsModel;
use App\Model\Virtual\VirtualtagModel;
use App\Service\CompanyService;
use App\Service\CpuService;
use App\Service\OsService;
use App\Service\Virtual\VirtualtagService;
use ValueError;

class ComputerModel extends MainModel {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
    */
    protected string $computer_id;

    /** @fk GET company_name SET company_id
     * @pattern ~^(?=.*\S)(.+)$~
    */
    protected CompanyModel $company;
    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $model;
    /** @valid int min 1901 max 2100 */
    protected int $year;
    /** @fk GET cpu_name SET cpu_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected CpuModel $cpu;
    /** @valid bcnum min 1 max inf */
    protected string $clock_hz;
    /** @valid bcnum min 1 max inf */
    protected string $ram_byte;
    /** @valid bcnum min 1 max inf */
    protected ?string $storage_byte = null;
    /** @fk GET os_name,os_version SET os_id
     *  @pattern ~^[ \t]*(.+\b)[ \t]*,[ \t]*(.+\b)[ \t]*$~
    */
    protected ?OsModel $os = null;
    /** @valid textarea
     *  @pattern ~^[\s\S]*$~
     */
    protected ?string $notes = null;
    /** @valid url */
    protected ?string $url = null;
    /** @fk GET tag_list SET null 
     *  @pattern ~^(?=.*\w.*)(?!.*,?[ '\w\d]{51,})([ ,'\w\d]+)$~
    */
    protected ?VirtualtagModel $tags = null;

    public function getCompany(): ?CompanyModel {

        return ($this->company) ?? null;
    }

    public function getCpu(): ?CpuModel {

        return ($this->cpu) ?? null;
    }

    public function getOs(): ?OsModel {

        return ($this->os) ?? null;
    }

    /**
     * Updates the FK objects if they are initialized as well as their PKs
     * @param array $fkServices contains the key/value pairs ['fk_name' => fkService]
     * @throws ValueError if the array is invalid
     */
    public function refresh(array $fkServices): void {

        if (
            (!isset($fkServices['company'])) || (!isset($fkServices['cpu'])) || (!isset($fkServices['os'])) || (!isset($fkServices['tags'])) 
            || 
            (!($fkServices['company'] instanceof CompanyService)) || (!($fkServices['cpu'] instanceof CpuService)) || (!($fkServices['os'] instanceof OsService)) || (!($fkServices['tags'] instanceof VirtualtagService))
        ) {

            throw new ValueError("Invalid Services array!");
        }

        if ((isset($this->company)) && (isset($this->company->company_id))) {

            // refresh only if the record is found
            $this->company = ($fkServices['company']->fetchById($this->company->company_id))?: $this->company;
        }

        if ((isset($this->cpu)) && (isset($this->cpu->cpu_id))) {

            // refresh only if the record is found
            $this->cpu = ($fkServices['cpu']->fetchById($this->cpu->cpu_id))?: $this->cpu;
        }

        if ((isset($this->os)) && (isset($this->os->os_id))) {

            // refresh only if the record is found
            $this->os = ($fkServices['os']->fetchById($this->os->os_id))?: $this->os;
        }

        // tags refresh
        $this->tags = ($fkServices['tags']->fetchById($this->computer_id))?: $this->tags;
    }

    public function getId(): string {
        
        return $this->computer_id;
    }

    public static function getIdName(): string {

        return 'computer_id';
    }
}