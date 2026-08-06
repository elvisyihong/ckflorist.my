<main class="maintenance-shell">
    <header class="maintenance-intro">
        <div class="brand brand-light" aria-label="<?= e($settings['business_name'] ?? 'CK Florist') ?>"><?= brand_mark($settings) ?></div>
        <p class="eyebrow">Website maintenance</p>
        <h1><?= e($settings['maintenance_title'] ?? 'Our menu is available') ?></h1>
        <?php if (!empty($settings['maintenance_message'])): ?>
            <p><?= nl2br(e($settings['maintenance_message'])) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($images !== []): ?>
        <section class="maintenance-menu-list" aria-label="Current menu">
            <?php foreach ($images as $index => $image): ?>
                <figure>
                    <a href="<?= e($image['path']) ?>" target="_blank" rel="noopener" aria-label="Open menu page <?= $index + 1 ?> at full size">
                        <img src="<?= e($image['path']) ?>" alt="<?= e($image['alt_text'] ?: 'CK Florist menu page ' . ($index + 1)) ?>" width="<?= (int) $image['width'] ?>" height="<?= (int) $image['height'] ?>" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                    </a>
                    <figcaption>Menu <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <p class="maintenance-empty">The menu is being updated. Please check again shortly.</p>
    <?php endif; ?>
</main>
