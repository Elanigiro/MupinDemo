<?php
declare(strict_types=1);
namespace App\Repository\Abstract;

use App\Repository\Utils\DBManager;

abstract class Repository {

    protected DBManager $myDB;

    public function beginTransaction(): void {

        $this->myDB->beginTransaction();
    }

    public function commitTransaction(): void {

        $this->myDB->commit();
    }

    public function rollbackTransaction(): void {

        $this->myDB->rollback();
    }
}