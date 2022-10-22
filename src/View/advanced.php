<?php $this->layout('layout', ['title' => 'Advanced Search', 'script' => ['advanced.dart.js'], 'styles' => ['advanced.css']]) ?>

<?php $this->start('aside') ?>
<?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
<header>
    <h1>Advanced Search</h1>
</header>
<?php $this->stop('heading') ?>

<?php

use App\Controller\Search; ?>
<main>
    <a id="std_src" href="/">Standard Search</a>
    <form id="adv_form" action="/search" method="POST">
        <?php $this->insert('component_resultmsg', ['errormsg' => "You have to fill at least one field!", 'divClass' => "tooltip", 'spanClass' => "tooltiptext"]); ?>
        <?php $this->insert('component_selection_list', ['catParamName' => Search::CAT_PARAM, 'default' => [Search::DEFAULT_POST, "-select an option-"], 'selection' => $selection, 'options' => $options]); ?>
        <?php $this->insert('component_advanced_form', ['selection_props' => $selection_props, 'selection_model' => $selection_model]); ?>
        <button type="submit" class="submit">Search</button>
    </form>
</main>