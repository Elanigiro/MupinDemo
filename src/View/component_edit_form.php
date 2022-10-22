<?php

use App\Controller\DeleteItem;
use App\Controller\EditItem;
use App\Controller\Pictures;
use App\Utils\MyReflectionUtils;

foreach (MyReflectionUtils::getProperties($item) as $prop) { ?>
    <section>
        <?php

        $propName = $prop->getName();

        $propInfo = MyReflectionUtils::getValidationInfo($item, $propName);

        $htmlType = '';
        $htmlMin = '';
        $htmlMax = '';
        $htmlReq = (($prop->hasType()) && (!$prop->getType()->allowsNull())) ? 'required' : '';
        $htmlPattern = 'pattern="' . $this->e($propInfo[3]) . '"';
        $htmlReadonly = ($item->isPK($propName)) ? 'readonly' : '';
        $htmlOpeningTag = '<input';
        $htmlClosingTag = '>';
        $htmlValue = 'value = "' . (($item->{$propName}) ?? '') . '"';

        switch ($propInfo[0]) {

            case 'int':
            case 'bcnum':
                $htmlType = 'type="number"';
                $htmlMin =  (ctype_digit($propInfo[1])) ? ('min="' . $propInfo[1] . '"') : '';
                $htmlMax =  (ctype_digit($propInfo[2])) ? ('max="' . $propInfo[2] . '"') : '';
                break;

            case 'url':
            case 'email':
                $htmlType = 'type="' . $propInfo[0] . '"';
                break;

            case 'tag':
                $htmlType = 'type="text"';
                break;

            case 'textarea':
                $htmlOpeningTag = '<textarea';
                $htmlClosingTag = '</textarea>';
                $htmlType = '';
                $htmlValue = '> ' . ($item->{$propName}) ?? '';
                break;

            default:
                $htmlType = 'input type="text"';
                $htmlMin =  (ctype_digit($propInfo[1])) ? ('minlength="' . $propInfo[1] . '"') : '';
                $htmlMax =  (ctype_digit($propInfo[2])) ? ('maxlength="' . $propInfo[2] . '"') : '';
                break;
        }

        ?>

        <div class="adv_label"><label for="<?= $propName ?>"><?= $propName ?></label></div>
        <?= $htmlOpeningTag ?> <?= $htmlType ?> <?= $htmlMin ?> <?= $htmlMax ?> id="<?= $propName ?>" name="<?= $propName ?>" <?= $htmlPattern ?> <?= $htmlReq ?> <?= $htmlReadonly ?> placeholder="..." class="adv_field" <?= $htmlValue ?> <?= $htmlClosingTag ?>
    </section>
<?php } ?>

<?php //model class (hidden control) ?>
<input type="hidden" name="<?= EditItem::MODEL_PARAM ?>" value="<?= get_class($item) ?>">

<section>
    <?php //edit pictures link ?>
    <a href="/pictures?<?= Pictures::ITEM_PARAM ?>=<?= $item->getId() ?>" title="click to show and edit all the related pictures">Edit pictures</a>
    <?php //delete item link ?>
    <a href="/deleteitem?<?= DeleteItem::ITEM_PARAM ?>=<?= $item->getId() ?>" title="click to delete the item">Delete Item</a>
</section>