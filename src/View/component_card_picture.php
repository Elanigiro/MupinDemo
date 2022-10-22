<?php

use App\Controller\Pictures;
use App\Model\Abstract\MainModel;
use App\Utils\MyPicsUtils;

$imgFound = (MyPicsUtils::findPicsById($itemPK, sorted: true)[0]) ?? false;
if ($imgFound) { ?>

    <?php //I add the main picture and link to all pictures ?>
    <section class="item_picture">
        <a href="/pictures?<?= Pictures::ITEM_PARAM ?>=<?= $itemPK ?>" title="click on the img to show <?= (isset($user))? 'and edit ' : '' ?>all the related pictures"><img src="/img/mupin/<?= basename($imgFound) ?>"></a>
    </section>
<?php } ?>