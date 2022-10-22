<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class InventoryModel extends Model {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
    */
    protected string $inv_id;

    /** @fk GET table_name SET cat_id*/
    protected CategoryModel $cat;

    public function getCategory(): ?CategoryModel {

        return ($this->cat) ?? null;
    }
}