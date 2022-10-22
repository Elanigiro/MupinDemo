<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Abstract\Model;

class TaginventoryModel extends Model {

    /** 
     * @pk 
     * @fk GET cat SET inv_id
     * @pattern ~^([A-Za-z0-9]+)$~
    */
    protected InventoryModel $inv;
    /** 
     * @pk 
     * @fk GET tag_string SET tag_id
     * @pattern ~^\b([ '\w]*)\b$~
    */
    protected TagModel $tag;
}