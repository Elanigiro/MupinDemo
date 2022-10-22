<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class CategoryModel extends Model {

    /** @pk */
    protected int $cat_id;
    
    protected string $table_name;
}