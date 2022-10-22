<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class CompanyModel extends Model {

    /** @pk */
    protected int $company_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $company_name;
    protected ?string $notes = null;
}