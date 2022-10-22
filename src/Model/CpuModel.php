<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class CpuModel extends Model {

    /** @pk */
    protected int $cpu_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $cpu_name;
    /** @valid int min 1901 max 2100 */
    protected ?int $first_manufactured = null;
    /** @fk GET company_name SET company_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected ?CompanyModel $company = null;
    protected ?string $notes = null;
}