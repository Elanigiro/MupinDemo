<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class SoftwaretypeModel extends Model {

    /** @pk */
    protected int $type_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $type_name;
}