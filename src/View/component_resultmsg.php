<?php

$mainClass = (isset($divClass))? (' class="' . $this->e($divClass) . '" ' ) : ('');
$secondClass = (isset($spanClass))? (' class="' . $this->e($spanClass) . '" ' ) : ('');

if (!empty($errormsg)) {
?>

    <div <?= $mainClass ?>><span <?= $secondClass ?>><?= $this->e($errormsg) ?></span></div>

<?php
}
?>