<?php
declare(strict_types=1);
namespace App\Repository\Abstract;

use App\Alias\MyPDODataAlias;
use App\Repository\Utils\DBManager;

abstract class RepositoryData extends Repository {

    public function __construct(MyPDODataAlias $pdo) {

        $this->myDB = new DBManager($pdo);
    }

    abstract static protected function getModelClass(): string;
}