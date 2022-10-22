<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class TagModel extends Model {

    /** @pk */
    protected int $tag_id;

    /** @pattern ~^\b([ '\w]*)\b$~ */
    protected string $tag_string;
}