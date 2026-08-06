<?php

use App\Core\Csrf;
use App\Support\DemoData;

$settings = $settings ?? DemoData::settings();
$title = $title ?? 'CK Florist';
$description = $description ?? 'Florist craft and café warmth in Brunei. Browse inspiration, customise a bouquet, and send a structured WhatsApp enquiry.';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#17372d">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($title) ?> · <?= e($settings['business_name'] ?? 'CK Florist') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <?php if ((parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/') === '/'): ?>
    <script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <?php endif; ?>
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="page-<?= e(trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: 'home') ?>">
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header" data-header>
    <a class="brand" href="/" aria-label="CK Florist home"><span>CK</span> Florist</a>
    <nav class="desktop-nav" aria-label="Primary navigation">
        <a href="/florist">Florist</a><a href="/customise">Customise</a><a href="/cafe">Café</a><a href="/gallery">Gallery</a><a href="/about">Our story</a>
    </nav>
    <a class="nav-selection" href="/selection">Selection <span data-selection-count><?= selected_count() ?></span></a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle><span class="sr-only">Open menu</span><i></i><i></i></button>
</header>
<nav class="mobile-menu" id="mobile-menu" aria-label="Mobile navigation" hidden data-mobile-menu>
    <a href="/florist">Florist inspiration</a><a href="/customise">Customise a bouquet</a><a href="/cafe">Café menu</a><a href="/gallery">Gallery</a><a href="/about">Our story</a><a href="/contact">Contact</a>
</nav>
<main id="main-content" class="site-main">
    <?= $content ?>
</main>
<footer class="site-footer">
    <div class="footer-cta">
        <p>Flowers are personal. Your enquiry should be too.</p>
        <a class="button button-light" href="/customise">Begin your bouquet</a>
    </div>
    <div class="footer-grid">
        <div><a class="brand brand-light" href="/"><span>CK</span> Florist</a><p>Florist craft and café warmth, made for thoughtful moments.</p></div>
        <div><h2>Visit</h2><p><?= nl2br(e($settings['address'] ?? 'Brunei Darussalam')) ?></p><a href="/contact">Hours and directions</a></div>
        <div><h2>Details</h2><a href="/policies/terms">Terms</a><a href="/policies/privacy">Privacy</a><a href="/policies/cancellation">Cancellation</a><a href="/admin/login">Admin</a></div>
    </div>
    <p class="footer-note">© <?= date('Y') ?> CK Florist. Sending an enquiry does not confirm an order.</p>
</footer>
<nav class="bottom-nav" aria-label="Quick navigation">
    <a href="/"><span aria-hidden="true">⌂</span>Home</a><a href="/florist"><span aria-hidden="true">✿</span>Florist</a><a class="bottom-nav-primary" href="/customise"><span aria-hidden="true">＋</span>Build</a><a href="/cafe"><span aria-hidden="true">◌</span>Café</a><a href="/selection"><span aria-hidden="true">▱</span><span>Selection</span><b data-selection-count><?= selected_count() ?></b></a>
</nav>
<div class="toast-region" aria-live="polite" aria-atomic="true" data-toast-region></div>
<?php if ($message = flash('success')): ?><script>window.ckfFlash={type:'success',message:<?= json_encode($message) ?>}</script><?php endif; ?>
<?php if ($message = flash('error')): ?><script>window.ckfFlash={type:'error',message:<?= json_encode($message) ?>}</script><?php endif; ?>
</body>
</html>
