<?php
$price = $product['promotional_price'] ?? $product['regular_price'];
$label = $product['promotional_price'] !== null ? 'Offer' : (!empty($product['is_featured']) ? 'Popular' : '');
$searchText = strtolower(implode(' ', [$product['name'], $product['category_name'], $product['description']]));
?>
<article class="cafe-card" data-cafe-card data-category="<?= e($product['category_slug'] ?? '') ?>" data-search="<?= e($searchText) ?>">
    <button class="cafe-card-button" type="button" aria-label="View <?= e($product['name']) ?> details" data-open-product='<?= e(json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
        <span class="cafe-card-image">
            <img src="<?= e($product['cover_image'] ?: '/public/assets/images/cafe-900.webp') ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="900" height="900">
            <?php if ($label !== ''): ?><b><?= e($label) ?></b><?php endif; ?>
        </span>
        <span class="cafe-card-body"><strong><?= e($product['name']) ?></strong><span><?= e(money($price)) ?><?php if ($product['promotional_price'] !== null): ?> <del><?= e(money($product['regular_price'])) ?></del><?php endif; ?></span></span>
    </button>
</article>
