<?php

$title = $title ?? 'Our menu is available';
$businessName = $settings['business_name'] ?? 'CK Florist';
$favicon = brand_favicon($settings);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#102c24">
    <title><?= e($title) ?> · <?= e($businessName) ?></title>
    <?php if ($favicon !== ''): ?><link rel="icon" href="<?= e($favicon) ?>"><link rel="apple-touch-icon" href="<?= e($favicon) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="maintenance-body">
    <?= $content ?>
</body>
</html>
