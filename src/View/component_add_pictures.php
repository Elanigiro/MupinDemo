<?php use App\Utils\MyPicsUtils; ?>
<?php

$htmlRequired = (isset($req))? 'required' : '';

if ($show) {
?>

    <section>
        <div class="adv_label"><label for="<?= $pics_param ?>">pictures</label></div>
        <input accept="<?= MyPicsUtils::IMG_EXTENSIONS ?>" type="file" id="<?= $pics_param ?>" name="<?= $pics_param ?>" multiple="multiple" <?= $htmlRequired ?>>
    </section>

<?php
}
?>