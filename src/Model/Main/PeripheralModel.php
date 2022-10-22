<?php
declare(strict_types=1);
namespace App\Model\Main;

use App\Model\Abstract\MainModel;
use App\Model\CompanyModel;
use App\Model\PeripheraltypeModel;
use App\Model\Virtual\VirtualtagModel;
use App\Service\CompanyService;
use App\Service\PeripheraltypeService;
use App\Service\Virtual\VirtualtagService;
use ValueError;

class PeripheralModel extends MainModel {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
    */
    protected string $periph_id;

    /** @fk GET company_name SET company_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected CompanyModel $company;
    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $model;
    /** @fk GET type_name SET type_id
     * @pattern ~^(?=.*\S)(.+)$~
    */
    protected PeripheraltypeModel $type;
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

    public function getPeripheraltype(): ?PeripheraltypeModel {

        return ($this->type) ?? null;
    }

    /**
     * Updates the FK objects if they are initialized as well as their PKs
     * @param array $fkServices contains the key/value pairs ['fk_name' => fkService]
     * @throws ValueError if the array is invalid
     */
    public function refresh(array $fkServices): void {

        if (
            (!isset($fkServices['company'])) || (!isset($fkServices['type'])) || (!isset($fkServices['tags'])) 
            || 
            (!($fkServices['company'] instanceof CompanyService)) || (!($fkServices['type'] instanceof PeripheraltypeService)) || (!($fkServices['tags'] instanceof VirtualtagService))
        ) {

            throw new ValueError("Invalid Services array!");
        }

        if ((isset($this->company)) && (isset($this->company->company_id))) {

            // refresh only if the record is found
            $this->company = ($fkServices['company']->fetchById($this->company->company_id))?: $this->company;
        }

        if ((isset($this->type)) && (isset($this->type->type_id))) {

            // refresh only if the record is found
            $this->type = ($fkServices['type']->fetchById($this->type->type_id))?: $this->type;
        }

        // tags refresh
        $this->tags = ($fkServices['tags']->fetchById($this->periph_id))?: $this->tags;
    }

    public function getId(): string {
        
        return $this->periph_id;
    }

    public static function getIdName(): string {

        return 'periph_id';
    }
}