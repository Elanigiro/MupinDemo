<?php if(empty($results)) { ?>
    <h3 id="no_result">No corresponding items!</h3>
<?php } 
      else { 
?>
    <?php foreach ($results as $item) { ?>
        <?php $this->insert('component_search_card', ['item' => $item, 'user' => $user]) ?>
    <?php } ?>
<?php }?>

