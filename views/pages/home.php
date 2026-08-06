<section class="hero">
    <picture class="hero-media"><source media="(max-width: 720px)" srcset="/public/assets/images/hero-960.webp"><img src="/public/assets/images/hero-1920.webp" alt="Blush and cream bouquet beside a ceramic café cup" width="1920" height="1280" fetchpriority="high"></picture>
    <div class="hero-copy"><p class="eyebrow">Florist & café · Brunei</p><h1>Flowers for the feeling <em>words cannot hold.</em></h1><p>Browse the garden, shape a bouquet around your moment, and pair it with something from our café table.</p><div class="button-row"><a class="button" href="/customise">Customise a bouquet</a><a class="button button-ghost" href="/florist">Explore inspiration</a></div></div>
    <div class="hero-detail"><span>Freshly composed</span><strong>Every enquiry begins with your story.</strong></div>
</section>
<div class="flower-marquee" aria-hidden="true"><div><span>Roses</span><i></i><span>Tulips</span><i></i><span>Hydrangea</span><i></i><span>Botanical café</span><i></i><span>Thoughtful gifting</span><i></i><span>Roses</span><i></i><span>Tulips</span><i></i><span>Hydrangea</span></div></div>
<section class="home-intro section-wrap"><p class="eyebrow">Made around the moment</p><h2>A bouquet is not a product code. It is a conversation in colour, shape and season.</h2></section>
<section class="service-bento section-wrap" aria-label="Discover CK Florist">
    <?php $feature = $samples[0] ?? null; ?>
    <article class="bento-feature"><?php if ($feature): ?><img src="<?= e($feature['cover_image']) ?>" alt="<?= e($feature['name']) ?>" loading="lazy"><div><p>Florist inspiration</p><h2><?= e($feature['name']) ?></h2><p><?= e($feature['description']) ?></p><a class="text-link" href="/florist/<?= e($feature['slug']) ?>">See the arrangement</a></div><?php endif; ?></article>
    <article class="bento-cafe"><img src="/public/assets/images/cafe-900.webp" alt="Rose latte and pistachio financier" loading="lazy"><div><h2>Stay for something botanical.</h2><a class="text-link" href="/cafe">Browse the café</a></div></article>
    <article class="bento-build"><p>Unsure where to begin?</p><h2>Choose the flowers. We will find the closest inspiration.</h2><a class="button button-light" href="/customise">Start with your occasion</a></article>
</section>
<section class="story-section section-wrap" data-story>
    <div class="story-title"><p class="eyebrow">Our way of working</p><h2>Personal, seasonal, considered.</h2></div>
    <div class="story-cards">
        <article><span>01</span><h3>Begin with a feeling</h3><p>Tell us who it is for, the occasion and the mood you hope to create.</p></article>
        <article><span>02</span><h3>Find visual language</h3><p>Our matching system surfaces relevant references without pretending every stem is fixed stock.</p></article>
        <article><span>03</span><h3>Confirm with a florist</h3><p>We review seasonality, shade, timing and budget with you in WhatsApp before anything is confirmed.</p></article>
    </div>
</section>
<section class="story-reveal section-wrap"><p data-reveal-text>Our flowers change with the market and the season. That is not a compromise; it is how each bouquet stays alive, responsive and truly yours.</p><a class="button button-outline" href="/about">Read our story</a></section>

