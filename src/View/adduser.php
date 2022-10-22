<?php $this->layout('layout', ['title' => 'Add User']) ?>

<?php $this->start('aside') ?>
    <?php $this->insert('component_sidebar', ['not_home' => true, 'user' => $user]); ?>
<?php $this->stop('aside') ?>

<?php $this->start('heading') ?>
    <header>
        <h1>Add User</h1>
    </header> 
<?php $this->stop('heading') ?>

<?php use App\Utils\MyPasswordUtils; ?>
<main>
    <?php $this->insert('component_userForm', ['status' => $status, 'status_msg' => ($status_msg)?? null, 'action' => '/adduser', 'method' => 'POST',
                                               'pwd_constraints' => "password MUST contain between " . MyPasswordUtils::MIN_LEN . " and " . MyPasswordUtils::MAX_LEN . " characters with at least one lowercase, uppercase letter, number and special character",
                                               'submit' => "Add"]); ?>
</main>