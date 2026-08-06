<article class="cafe-card" data-cafe-card>
    <div class="cafe-card-image"><img src="<?= e($product['cover_image'] ?: '/public/assets/images/cafe-900.webp') ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="900" height="900"></div>
    <div class="cafe-card-body"><p><?= e($product['category_name']) ?></p><h3><?= e($product['name']) ?></h3><p><?= e($product['description']) ?></p><div class="cafe-meta"><strong><?= e(money($product['promotional_price'] ?? $product['regular_price'])) ?></strong><?php foreach ($product['dietary_labels'] as $label): ?><span class="badge"><?= e($label) ?></span><?php endforeach; ?></div><button class="button button-outline button-block" type="button" data-open-product='<?= e(json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>Choose options</button></div>
</article>

