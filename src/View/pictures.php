<?php

use App\Controller\EditPics;

$this->layout('layout', ['title' => "Item Pictures", 'styles' => ['pictures.css']]) ?>

<?php $this->start('aside') ?>
<?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
<header>
    <h1> Item Pictures</h1>
</header>
<?php $this->stop('heading') ?>

<main>
    <?php $tmpHeading = (empty($pics)) ? "<h3> No pictures found! </h3>"  : "<h3>Item Id: " . $this->e($itemId) . "</h3>" ?>
    <?= $tmpHeading ?>

    <?php // shown only to authenticated users ?>
    <?php if (isset($user)) { ?>
        <?php $this->insert('component_resultmsg', ['errormsg' => ($msg ?? ''), 'divClass' => ("tooltip " . ($msg ?? '')), 'spanClass' => "tooltiptext"]); ?>
    <?php } ?>

    <?php $this->insert('component_pictures', ['pics' => $pics, 'user' => $user]) ?>

    <?php // shown only to authenticated users ?>
    <?php if (isset($user)) { ?>
        <form action="/editpics" method="POST" enctype="multipart/form-data">
            <?php $this->insert('component_add_pictures', ['show' => true, 'pics_param' => EditPics::ADD_PARAM_HTML, 'req' => true]); ?>
            <input type="hidden" name="<?= EditPics::ITEM_PARAM ?>" value="<?= $this->e($itemId) ?>">
            <button type="submit">Add</button>
        </form>
    <?php } ?>
</main>