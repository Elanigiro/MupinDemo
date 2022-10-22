  
    <aside id="sidebar">
        <?php if($not_home) { ?>
            <div><a href="/"> Home </a></div>
        <?php } ?>
        <?php if(isset($user)) { ?>
            <div> <a href="/logout">Logout</a> <?= ($this->e($user)) ?> </div>
            <div> <a href="/adduser">Add User</a> </div>
            <div> <a href="/deleteuser">Delete User</a> </div>
            <div> <a href="/additem">Add Item to DB</a> </div>
        <?php }
        else { ?>
            <div> <a href="/login">Login</a> </div>
        <?php }?>

    </aside>
