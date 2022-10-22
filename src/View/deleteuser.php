<?php $this->layout('layout', ['title' => 'Delete User']) ?>

<?php $this->start('aside') ?>
    <?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
    <header>
        <h1>Delete User</h1>
    </header> 
<?php $this->stop('heading') ?>

<main>
    <?php $this->insert('component_userForm', ['status' => $status, 'status_msg' => ($status_msg)?? null, 'action' => '/deleteuser', 'method' => 'POST','pwd_constraints' => "", 'submit' => "Delete"]); ?>
</main>