<?php
use App\Core\Auth;

$user = Auth::user();
$brandSettings = shop_settings();
$favicon = brand_favicon($brandSettings);
$adminNavigation = [
    '/admin' => 'Dashboard',
    '/admin/maintenance' => 'Maintenance mode',
    '/admin/enquiries' => 'Enquiries',
    '/admin/florist-samples' => 'Florist samples',
    '/admin/sample-flowers' => 'Sample flowers',
    '/admin/sample-images' => 'Sample images',
    '/admin/sample-colours' => 'Sample colours',
    '/admin/sample-occasions' => 'Sample occasions',
    '/admin/flowers' => 'Flower categories',
    '/admin/arrangements' => 'Arrangements',
    '/admin/colours' => 'Colours',
    '/admin/occasions' => 'Occasions',
    '/admin/bouquet-sizes' => 'Bouquet sizes',
    '/admin/wrapping-papers' => 'Wrapping',
    '/admin/decorations' => 'Decorations',
    '/admin/cafe-categories' => 'Café categories',
    '/admin/cafe-products' => 'Café products',
    '/admin/cafe-options' => 'Café options',
    '/admin/banners' => 'Banners',
    '/admin/gallery' => 'Gallery',
    '/admin/media' => 'Media',
    '/admin/policies' => 'Policies',
    '/admin/settings' => 'Settings',
];
$currentAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?= e(App\Core\Csrf::token()) ?>">
    <title><?= e($title ?? 'Admin') ?> · CK Florist</title>
    <?php if ($favicon !== ''): ?><link rel="icon" href="<?= e($favicon) ?>"><link rel="apple-touch-icon" href="<?= e($favicon) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="admin-body">
    <a class="skip-link" href="#admin-main">Skip to content</a>

    <aside class="admin-sidebar" id="admin-navigation" data-admin-navigation>
        <header class="admin-sidebar-head">
            <a class="brand brand-light admin-brand" href="/admin"><?= brand_mark($brandSettings) ?><small>Admin</small></a>
            <button class="admin-nav-close" type="button" aria-label="Close navigation" data-admin-nav-close>×</button>
        </header>
        <nav aria-label="Admin navigation">
            <?php foreach ($adminNavigation as $path => $label):
                $isCurrent = $path === '/admin' ? $currentAdminPath === $path : str_starts_with($currentAdminPath, $path);
            ?>
                <a href="<?= e($path) ?>" <?= $isCurrent ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <button class="admin-nav-backdrop" type="button" aria-label="Close navigation" data-admin-nav-backdrop hidden></button>

    <div class="admin-frame">
        <header class="admin-topbar">
            <div class="admin-topbar-identity">
                <button class="admin-nav-toggle" type="button" aria-label="Open navigation" aria-controls="admin-navigation" aria-expanded="false" data-admin-nav-toggle>
                    <i></i><i></i><i></i>
                </button>
                <div><p><?= e($user['role'] ?? '') ?></p><strong><?= e($user['name'] ?? '') ?></strong></div>
            </div>
            <form action="/admin/logout" method="post"><?= csrf_field() ?><button class="button button-small button-outline" type="submit">Sign out</button></form>
        </header>
        <main id="admin-main" class="admin-main"><?= $content ?></main>
    </div>

    <div class="toast-region" aria-live="polite" data-toast-region></div>
    <?php if ($m = flash('success')): ?><script>window.ckfFlash={type:'success',message:<?= json_encode($m) ?>}</script><?php endif; ?>
    <?php if ($m = flash('error')): ?><script>window.ckfFlash={type:'error',message:<?= json_encode($m) ?>}</script><?php endif; ?>
</body>
</html>
