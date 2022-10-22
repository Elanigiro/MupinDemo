<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class AuthorModel extends Model {

    /** @pk */
    protected int $aut_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $first_name;
    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $last_name;
}