<?php

use App\Utils\MyPasswordUtils; ?>

<?php
if ($status === "success") {

    $msg_html = '<div class="success"> User Added!</div>';
} else if ($status === "success_delete") {

    $msg_html = '<div class="success"> User Deleted!</div>';
} else if ($status === "failure") {

    $tmp_msg = ($status_msg) ?? 'Invalid Email/Password!';
    $msg_html = '<div class="failure">' . $tmp_msg . '</div>';
} else {

    $msg_html = null;
}

if (!empty($pwd_constraints)) {

    $patternHtml = 'pattern="' . $this->e(MyPasswordUtils::VALIDATION_PATTERN_HTML) . '"';
    $minHtml = 'minlength="' . MyPasswordUtils::MIN_LEN . '"';
    $maxHtml = 'maxlength="' . MyPasswordUtils::MAX_LEN . '"';
}
?>
<?= $msg_html ?>
<form action="<?= $action ?>" method="<?= $method ?>">
    <label for="email">Email: </label>
    <input type="email" id="email" name="email" required>
    <label for="pwd">Password: </label>
    <input type="password" id="pwd" name="pwd" title="<?= $pwd_constraints ?>" <?= ($patternHtml) ?? '' ?> <?= ($minHtml) ?? '' ?> <?= ($maxHtml) ?? '' ?> required>
    <button type="submit"><?= $submit ?></button>
</form>