<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class PublisherModel extends Model {

    /** @pk */
    protected int $pub_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $pub_name;
}