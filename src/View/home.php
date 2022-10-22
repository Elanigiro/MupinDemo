<?php $this->layout('layout', ['title' => 'Home MUPIN']) ?>

<?php $this->start('aside') ?>
    <?php $this->insert('component_sidebar', ['not_home' => false, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
    <header>
        <h1>MUPIN</h1>
    </header>   
<?php $this->stop('heading') ?>

<?php use App\Controller\Search; ?>
<main>
    <a id="adv_src" href="/advanced">Advanced Search</a>
    <form action="/search" method="GET">
        <?php $this->insert('component_selection_list', ['catParamName' => Search::CAT_PARAM, 'default' => [Search::DEFAULT_GET, Search::DEFAULT_GET], 'selection' => $selection, 'options' => $options]) ?>
        <input type="text" name="<?= Search::SEARCH_PARAM ?>" maxlength="<?= Search::SEARCH_PARAM_MAX_LEN ?>" required>
        <button type="submit">Search</button>
    </form>
</main>
