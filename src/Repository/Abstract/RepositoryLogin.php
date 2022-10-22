<?php
declare(strict_types=1);
namespace App\Repository\Abstract;

use App\Alias\MyPDOLoginAlias;
use App\Repository\Utils\DBManager;

abstract class RepositoryLogin extends Repository {

    public function __construct(MyPDOLoginAlias $pdo) {

        $this->myDB = new DBManager($pdo);
    }
}