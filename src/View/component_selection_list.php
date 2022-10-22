
<?php 
 // This component populates the selection list with the categories from the Database. It then selects the option sent via query string or default as fallback
?>

<select name="<?= $catParamName ?>" id="cat_select" required>
    <option value="<?= $default[0] ?>" <?= ($selection === false)? "selected" : "" ?>><?= $default[1] ?></option>
    <?php for($i = 0; $i < count($options); ++$i) { ?>
        <option value="<?= $this->e($options[$i]->table_name) ?>" <?= ($selection === $i)? "selected" : "" ?>><?= $this->e($options[$i]->table_name) ?></option>
    <?php } ?>
</select>