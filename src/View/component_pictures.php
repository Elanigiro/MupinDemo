<?php

use App\Controller\EditPics;

foreach ($pics as $pic) {

    $baseName = basename($pic);
    $tmpurl = "/img/mupin/$baseName";
    $htmlLink = (isset($user)) ? ('<a id="delpicanchor" href="editpics?' . EditPics::DEL_PARAM . '=' . $baseName . '">Delete</a>') : '';

?>
    <section>
        <figure class="item_pic">
            <img src="<?= $tmpurl ?>">
            <figcaption><?= $baseName ?></figcaption>
        </figure>
        <?= $htmlLink ?>
    </section>
<?php } ?>