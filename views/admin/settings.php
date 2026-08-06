<header class="admin-page-head">
    <div><p class="eyebrow">Public configuration</p><h1>Shop settings</h1></div>
</header>
<form class="admin-form" method="post" action="/admin/settings" data-brand-settings>
    <?= csrf_field() ?>
    <?php $logoUrl = brand_asset_url($settings, 'logo'); $faviconUrl = brand_asset_url($settings, 'favicon'); ?>
    <section class="admin-panel">
        <div class="brand-settings-head"><p class="eyebrow">Brand identity</p><h2>Logo and favicon</h2><p>Upload replacements here, then save settings. The logo is shared across the website, maintenance page, Admin and sign-in screen.</p></div>
        <div class="brand-asset-grid">
            <article class="brand-asset-card" data-brand-asset="logo">
                <div class="brand-asset-preview"><img src="<?= e($logoUrl) ?>" alt="Current shop logo" <?= $logoUrl === '' ? 'hidden' : '' ?> data-brand-preview><span <?= $logoUrl !== '' ? 'hidden' : '' ?> data-brand-placeholder>CK</span></div>
                <div><h3>Shop logo</h3><p>Used in navigation, footer, maintenance and Admin.</p></div>
                <input type="hidden" name="logo" value="<?= e($logoUrl) ?>" data-brand-value>
                <label class="button button-small">Upload logo<input class="sr-only" type="file" accept="image/jpeg,image/png,image/webp,image/avif" data-brand-upload="logo"></label>
                <button class="button button-small button-outline" type="button" data-brand-remove="logo">Remove</button>
            </article>
            <article class="brand-asset-card" data-brand-asset="favicon">
                <div class="brand-asset-preview brand-asset-preview-small"><img src="<?= e($faviconUrl) ?>" alt="Current favicon" <?= $faviconUrl === '' ? 'hidden' : '' ?> data-brand-preview><span <?= $faviconUrl !== '' ? 'hidden' : '' ?> data-brand-placeholder>CK</span></div>
                <div><h3>Browser favicon</h3><p>Used in browser tabs and saved shortcuts. Falls back to the logo when empty.</p></div>
                <input type="hidden" name="favicon" value="<?= e($faviconUrl) ?>" data-brand-value>
                <label class="button button-small">Upload favicon<input class="sr-only" type="file" accept="image/jpeg,image/png,image/webp,image/avif" data-brand-upload="favicon"></label>
                <button class="button button-small button-outline" type="button" data-brand-use-logo>Use shop logo</button>
                <button class="button button-small button-outline" type="button" data-brand-remove="favicon">Remove</button>
            </article>
        </div>
        <p class="upload-status" aria-live="polite" data-brand-upload-status></p>
    </section>
    <section class="admin-panel form-fields">
        <?php
        $fields = [
            'business_name' => 'Business name', 'whatsapp_number' => 'WhatsApp number',
            'telephone' => 'Telephone', 'email' => 'Email', 'address' => 'Address',
            'google_maps_url' => 'Google Maps URL', 'map_embed' => 'Map embed', 'currency' => 'Currency',
            'delivery_information' => 'Delivery information', 'pickup_information' => 'Pickup information',
            'florist_disclaimer' => 'Florist disclaimer', 'cancellation_policy' => 'Cancellation policy',
            'terms' => 'Terms', 'privacy_policy' => 'Privacy policy',
        ];
        $longFields = ['address','map_embed','delivery_information','pickup_information','florist_disclaimer','cancellation_policy','terms','privacy_policy'];
        foreach ($fields as $key => $label):
        ?>
            <label class="field <?= in_array($key, $longFields, true) ? 'field-full' : '' ?>">
                <span><?= e($label) ?></span>
                <?php if (in_array($key, $longFields, true)): ?>
                    <textarea name="<?= e($key) ?>"><?= e($settings[$key] ?? '') ?></textarea>
                <?php else: ?>
                    <input name="<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>">
                <?php endif; ?>
            </label>
        <?php endforeach; ?>
        <label class="field field-full"><span>Business hours JSON</span><textarea name="business_hours"><?= e(json_encode($settings['business_hours'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea></label>
        <label class="field field-full"><span>Social links JSON</span><textarea name="social_links"><?= e(json_encode($settings['social_links'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea></label>
        <label class="field field-full"><span class="toggle"><input type="checkbox" name="allow_combined_enquiries" value="1" <?= !empty($settings['allow_combined_enquiries']) ? 'checked' : '' ?>><i></i>Allow combined florist and café enquiries</span></label>
    </section>
    <footer class="admin-save-bar"><button class="button" type="submit">Save settings</button></footer>
</form>
