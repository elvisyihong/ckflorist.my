<?php
$offerSlides = [];
foreach ($banners as $banner) {
    $link = trim((string) ($banner['link_url'] ?? ''));
    $offerSlides[] = [
        'label' => 'Special offer',
        'title' => (string) $banner['title'],
        'body' => (string) ($banner['body'] ?? ''),
        'image' => (string) ($banner['image'] ?: '/public/assets/images/cafe-900.webp'),
        'link' => preg_match('#^(?:/(?!/)|https://)#', $link) ? $link : '',
        'link_label' => (string) ($banner['link_label'] ?: 'View offer'),
        'product' => null,
    ];
}
if ($offerSlides === []) {
    foreach ($products as $product) {
        if (empty($product['is_featured']) && $product['promotional_price'] === null) continue;
        $offerSlides[] = [
            'label' => $product['promotional_price'] !== null ? 'Limited offer' : 'Café favourite',
            'title' => $product['name'],
            'body' => $product['promotional_price'] !== null
                ? money($product['promotional_price']) . ' for a limited time'
                : $product['description'],
            'image' => $product['cover_image'],
            'link' => '',
            'link_label' => 'View item',
            'product' => $product,
        ];
        if (count($offerSlides) >= 3) break;
    }
}
?>
<div class="cafe-app" data-cafe-menu>
    <header class="cafe-menu-head">
        <p class="eyebrow">CK Florist Café</p>
        <h1>What would you like?</h1>
        <label class="cafe-search">
            <span class="sr-only">Search the café menu</span>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
            <input type="search" placeholder="Search drinks and bakes" autocomplete="off" data-cafe-search>
        </label>
    </header>

    <?php if ($offerSlides !== []): ?>
    <section class="cafe-offers" aria-labelledby="cafe-offers-title">
        <div class="cafe-section-head"><h2 id="cafe-offers-title">Offers</h2><span>Swipe</span></div>
        <div class="cafe-offer-track" data-offer-track>
            <?php foreach ($offerSlides as $index => $offer): ?>
            <article class="cafe-offer-slide" data-offer-slide>
                <img src="<?= e($offer['image']) ?>" alt="<?= e($offer['title']) ?>" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                <div>
                    <p><?= e($offer['label']) ?></p>
                    <h3><?= e($offer['title']) ?></h3>
                    <?php if ($offer['body'] !== ''): ?><span><?= e($offer['body']) ?></span><?php endif; ?>
                    <?php if (is_array($offer['product'])): ?>
                        <button type="button" data-open-product='<?= e(json_encode($offer['product'], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'><?= e($offer['link_label']) ?></button>
                    <?php elseif ($offer['link'] !== ''): ?>
                        <a href="<?= e($offer['link']) ?>"><?= e($offer['link_label']) ?></a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php if (count($offerSlides) > 1): ?><div class="cafe-offer-dots" aria-label="Choose offer"><?php foreach ($offerSlides as $index => $offer): ?><button type="button" aria-label="Offer <?= $index + 1 ?>" class="<?= $index === 0 ? 'is-active' : '' ?>" data-offer-dot="<?= $index ?>"></button><?php endforeach; ?></div><?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="cafe-menu" aria-labelledby="cafe-menu-title">
        <div class="cafe-section-head"><h2 id="cafe-menu-title">Categories</h2><span data-cafe-result-count><?= count($products) ?> items</span></div>
        <nav class="cafe-categories" aria-label="Filter café menu">
            <button class="is-active" type="button" data-cafe-category="all">All</button>
            <?php foreach ($categories as $category): ?><button type="button" data-cafe-category="<?= e($category['slug']) ?>"><?= e($category['name']) ?></button><?php endforeach; ?>
        </nav>
        <div class="cafe-grid" data-cafe-grid><?php foreach ($products as $product) require BASE_PATH . '/views/components/cafe-card.php'; ?></div>
        <div class="cafe-empty" hidden data-cafe-empty><h3>No matching items</h3><p>Try another search or category.</p></div>
    </section>
</div>

<dialog class="product-sheet" data-product-sheet aria-labelledby="product-sheet-title">
    <header class="sheet-topbar">
        <form method="dialog" class="sheet-close-form"><button type="submit" aria-label="Close product details">←</button></form>
        <strong>Details</strong><span aria-hidden="true"></span>
    </header>
    <form data-product-form class="product-detail-form">
        <input type="hidden" name="product_id">
        <div class="sheet-image"><img data-product-image src="/public/assets/images/cafe-450.webp" alt=""><span data-product-label hidden></span></div>
        <div class="sheet-body">
            <div class="sheet-title-row">
                <div><p data-product-category></p><h2 id="product-sheet-title" data-product-name></h2></div>
                <div class="quantity-stepper" aria-label="Quantity"><button type="button" data-quantity-minus aria-label="Decrease quantity">−</button><input name="quantity" type="number" min="1" max="20" value="1" readonly aria-label="Quantity"><button type="button" data-quantity-plus aria-label="Increase quantity">＋</button></div>
            </div>
            <div class="sheet-price-line"><strong data-product-price></strong><del data-product-regular-price hidden></del></div>
            <div class="sheet-dietary" data-product-dietary></div>
            <div class="sheet-options" data-product-options></div>
            <section class="sheet-description"><h3>Description</h3><p data-product-description></p></section>
            <label class="field sheet-notes"><span>Special request <small>Optional</small></span><textarea name="notes" maxlength="300" placeholder="Milk preference or other request"></textarea></label>
        </div>
        <footer class="sheet-add-bar"><strong data-product-total-price></strong><button class="button" type="submit">Add to selection</button></footer>
    </form>
</dialog>
