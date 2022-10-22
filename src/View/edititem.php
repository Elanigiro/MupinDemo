<?php $this->layout('layout', ['title' => 'Edit DB Item', 'styles' => ['advanced.css']]) ?>

<?php $this->start('aside') ?>
<?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
<header>
    <h1>Edit Item</h1>
</header>
<?php $this->stop('heading') ?>

<main>
    <form id="edit_form" action="/edititem" method="POST">
        <?php $this->insert('component_resultmsg', ['errormsg' => ($msg?? ''), 'divClass' => ("tooltip " . ($msg ?? '')), 'spanClass' => "tooltiptext"]); ?>
        <?php if (!isset($msg)) { ?>
            <?php $this->insert('component_edit_form', ['item' => $item]); ?>
            <button type="submit" class="submit">Edit</button>
        <?php } ?>
    </form>
</main>