<article class="sample-card">
    <a class="sample-card-image" href="/florist/<?= e($sample['slug']) ?>">
        <img src="<?= e($sample['thumbnail'] ?? $sample['cover_image']) ?>" srcset="<?= e($sample['thumbnail'] ?? $sample['cover_image']) ?> 450w, <?= e($sample['cover_image']) ?> 900w" sizes="(max-width: 720px) 50vw, 33vw" alt="<?= e($sample['name']) ?> bouquet inspiration" loading="lazy" width="900" height="900">
        <?php if (!empty($sample['is_featured'])): ?><span class="image-badge">Florist highlight</span><?php endif; ?>
    </a>
    <div class="sample-card-body"><div><p><?= e(implode(' · ', $sample['flowers'] ?? [])) ?></p><h3><a href="/florist/<?= e($sample['slug']) ?>"><?= e($sample['name']) ?></a></h3></div><span><?= e(money($sample['estimated_price_min'])) ?>–<?= e(number_format((float) $sample['estimated_price_max'], 2)) ?></span></div>
</article>

