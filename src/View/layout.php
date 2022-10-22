<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/static/style/normalize.css">
    <link rel="stylesheet" href="/static/style/style.css">
    <?php if(isset($styles)) {

        foreach ($styles as $styleName) { ?>
    
            <link rel="stylesheet" href="/static/style/<?= $styleName ?>">
        <?php }
    }
    ?>
    <?php if(isset($script)) {

        foreach ($script as $fileName) { ?>
            
            <script defer src="/static/script/<?= $fileName ?>"></script>
       <?php }
    }
    ?>
    <title><?= $this->e($title) ?></title>
</head>

<body>

    <?= $this->section('aside') ?>
    <?= $this->section('heading') ?>
    <?= $this->section('content') ?>

</body>

</html>