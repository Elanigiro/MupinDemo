<?php
declare(strict_types=1);
namespace App\Model\Main;

use App\Model\Abstract\MainModel;
use App\Model\AuthorModel;
use App\Model\PublisherModel;
use App\Model\Virtual\VirtualtagModel;
use App\Service\AuthorService;
use App\Service\PublisherService;
use App\Service\Virtual\VirtualtagService;
use ValueError;

class BookModel extends MainModel {

    /** @pk 
     *  @valid string min 20 max 20
     *  @pattern ~^([A-Za-z0-9]+)$~
    */
    protected string $book_id;
    /**
     * @pattern ~^(?=.*\S)(.+)$~
    */
    protected string $title;
    /** @fk GET first_name,last_name SET aut_id
     *  @pattern ~^[ \t]*(.+\b)[ \t]*,[ \t]*(.+\b)[ \t]*$~
    */
    protected AuthorModel $author;
    /** @fk GET pub_name SET pub_id
     *  @pattern ~^(?=.*\S)(.+)$~
    */
    protected PublisherModel $publisher;
    /** @valid int min 1901 max 2100 */
    protected int $year;
    /** @valid int min 1 max inf */
    protected ?int $pages = null;
    
    /** @valid string min 13 max 13 
     *  @pattern ~^(\d+)$~
    */
    protected ?string $isbn = null;
    /** @valid textarea
     * @pattern ~^[\s\S]*$~
     */
    protected ?string $notes = null;
    /** @valid url */
    protected ?string $url = null;
    /** @fk GET tag_list SET null 
     *  @pattern ~^(?=.*\w.*)(?!.*,?[ '\w\d]{51,})([ ,'\w\d]+)$~
     */
    protected ?VirtualtagModel $tags = null;


    public function getAuthor() : ?AuthorModel {

        return ($this->author)?? null;
    }

    public function getPublisher() : ?PublisherModel {

        return ($this->publisher)?? null;
    }

    /**
     * Updates the FK objects if they are initialized as well as their PKs
     * @param array $fkServices contains the key/value pairs ['fk_name' => fkService]
     * @throws ValueError if the array is invalid
     */
    public function refresh(array $fkServices): void {

        if (
            (!isset($fkServices['author'])) || (!isset($fkServices['publisher'])) || (!isset($fkServices['tags']))
             || 
            (!($fkServices['author'] instanceof AuthorService)) || (!($fkServices['publisher'] instanceof PublisherService)) || (!($fkServices['tags'] instanceof VirtualtagService))
        ) {

            throw new ValueError("Invalid Services array!");
        }

        if ((isset($this->author)) && (isset($this->author->aut_id))) {

            // refresh only if the record is found
            $this->author = ($fkServices['author']->fetchById($this->author->aut_id))?: $this->author;
        }

        if ((isset($this->publisher)) && (isset($this->publisher->pub_id))) {

            // refresh only if the record is found
            $this->publisher = ($fkServices['publisher']->fetchById($this->publisher->pub_id))?: $this->publisher;
        }

        // tags refresh
        $this->tags = ($fkServices['tags']->fetchById($this->book_id))?: $this->tags;

    }

    public function getId(): string {
        
        return $this->book_id;
    }

    public static function getIdName(): string {

        return 'book_id';
    }
}