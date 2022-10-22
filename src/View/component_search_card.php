<?php

use App\Controller\EditItem;
use App\Utils\MyReflectionUtils; ?>

<article class="card">
    <div>
        <h3><?php preg_match('/(\w+)Model$/', $item::class, $category);
            echo $this->e($category[1]); ?></h3>
    </div>

    <?php foreach (MyReflectionUtils::getPropertiesName($item) as $prop) {
        if (isset($item->{$prop})) {

            $propName = $this->e($prop);
            $propValue = $this->e($item->{$prop});

            if ($item::isPK($prop)) { ?>

                <?php //I save the PK for later use ?>
                <?php $itemPK = $propValue; ?>

                <section>
                    <h4><?= $propName ?></h4>
                    <?php //If the user is logged in I set the PK as a link for item editing ?>
                    <?php if (isset($user)) { 

                        $tmpPKEditlink = '<a href="edititem?' . EditItem::ITEM_PARAM . '=' . $propValue . '" title="Edit the item">Edit item</a>';
                    }?>
                    
                    <p><?= $propValue ?> <?= ($tmpPKEditlink)?? '' ?></p>
                </section>

            <?php } else if ($item::isUrl($prop)) { ?>

                <section class="url">
                    <h4><?= $propName ?></h4>
                    <p><a href="<?= $propValue ?>"><?= $propValue ?></a></p>
                </section>

            <?php } else { ?>

                <section>
                    <h4><?= $propName ?></h4>
                    <p><?= $propValue ?></p>
                </section>

            <?php } ?>

        <?php } ?>
    <?php } ?>

    <?php
    if (isset($itemPK)) {

        $this->insert('component_card_picture', ['itemPK' => $itemPK, 'user' => $user ?? null]);
    }
    ?>

</article>