<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class OsModel extends Model {

    /** @pk */
    protected int $os_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $os_name;
    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $os_version;
    /** @fk GET company_name SET company_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected ?CompanyModel $company = null;
}