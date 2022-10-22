<?php $this->layout('layout', ['title' => 'Login']) ?>

<?php $this->start('aside') ?>
    <?php $this->insert('component_sidebar', ['not_home' => true, 'user' => ($user)?? null]); ?>  
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
    <header>
        <h1>Login</h2>
    </header>   
<?php $this->stop('heading') ?>

<main>
    <?php $this->insert('component_userForm', ['status' => $status, 'action' => '/login', 'method' => 'POST','pwd_constraints' => "", 'submit' => "Login"]); ?>
</main>