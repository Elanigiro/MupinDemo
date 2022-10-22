<?php $this->layout('layout', ['title' => 'Add Item to DB', 'script' => ['additem.dart.js'], 'styles' => ['advanced.css']]) ?>

<?php $this->start('aside') ?>
<?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
<header>
    <h1>Add Item</h1>
</header>
<?php $this->stop('heading') ?>

<?php use \App\Controller\AddItem; ?>
<main>
    <form id="add_form" action="/additem" method="POST" enctype="multipart/form-data">
        <?php $this->insert('component_resultmsg', ['errormsg' => ($msg ?? ''), 'divClass' => ("tooltip " . ($msg ?? '')), 'spanClass' => "tooltiptext"]); ?>
        <?php $this->insert('component_selection_list', ['catParamName' => AddItem::SELECTION_PARAM, 'default' => ["", "-select an option-"], 'selection' => $selection, 'options' => $options]) ?>
        <?php $this->insert('component_advanced_form', ['selection_props' => $selection_props, 'selection_model' => $selection_model, 'completeModel' => true]); ?>
        <?php $this->insert('component_add_pictures', ['show' => ($selection !== false) ? true : false, 'pics_param' => AddItem::FILE_PARAM_HTML]); ?>

        <button type="submit" class="submit">Add</button>
    </form>
</main>