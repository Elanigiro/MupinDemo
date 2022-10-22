<?php
declare(strict_types=1);
namespace App\Model\Main;

use App\Model\Abstract\MainModel;
use App\Model\PublisherModel;
use App\Model\Virtual\VirtualtagModel;
use App\Service\PublisherService;
use App\Service\Virtual\VirtualtagService;
use ValueError;

class MagazineModel extends MainModel {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
    */
    protected string $mag_id;

    /** @pattern ~^(?=.*\S)(.+)$~ */
    protected string $title;
    /** @valid int min 1 max inf */
    protected int $mag_no;
    /** @valid int min 1901 max 2100 */
    protected int $year;
    /** @fk GET pub_name SET pub_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected PublisherModel $publisher;
    /** @valid textarea
     *  @pattern ~^[\s\S]*$~
     */
    protected ?string $notes = null;
    /** @valid url */
    protected ?string $url = null;
    /** @fk GET tag_list SET null 
     *  @pattern ~^(?=.*\w.*)(?!.*,?[ '\w\d]{51,})([ ,'\w\d]+)$~
    */
    protected ?VirtualtagModel $tags = null;

    public function getPublisher(): ?PublisherModel {

        return ($this->publisher) ?? null;
    }

    /**
     * Updates the FK objects if they are initialized as well as their PKs
     * @param array $fkServices contains the key/value pairs ['fk_name' => fkService]
     * @throws ValueError if the array is invalid
     */
    public function refresh(array $fkServices): void {

        if (
            (!isset($fkServices['publisher'])) || (!isset($fkServices['tags'])) 
            || 
            (!($fkServices['publisher'] instanceof PublisherService)) || (!($fkServices['tags'] instanceof VirtualtagService))
        ) {

            throw new ValueError("Invalid Services array!");
        }

        if ((isset($this->publisher)) && (isset($this->publisher->pub_id))) {

            // refresh only if the record is found
            $this->publisher = ($fkServices['publisher']->fetchById($this->publisher->pub_id))?: $this->publisher;
        }

        // tags refresh
        $this->tags = ($fkServices['tags']->fetchById($this->mag_id))?: $this->tags;
    }

    public function getId(): string {
        
        return $this->mag_id;
    }

    public static function getIdName(): string {

        return 'mag_id';
    }
}