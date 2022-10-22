<?php $this->layout('layout', ['title' => 'Search Results', 'styles' => ['search.css']]) ?>

<?php $this->start('aside') ?>
    <?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
    <header>
        <h1>Search Results</h1>
    </header>   
<?php $this->stop('heading') ?>

<main>
    <a id="adv_src" href="/<?= $this->e($origin) ?>">New Search</a>
    <?php $this->insert('component_search_results', ['results' => $results, 'user' => $user]) ?>
</main>