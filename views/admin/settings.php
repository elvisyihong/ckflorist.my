<header class="admin-page-head">
    <div><p class="eyebrow">Public configuration</p><h1>Shop settings</h1></div>
</header>
<form class="admin-form" method="post" action="/admin/settings">
    <?= csrf_field() ?>
    <section class="admin-panel form-fields">
        <?php
        $fields = [
            'business_name' => 'Business name', 'logo' => 'Logo path or media reference',
            'favicon' => 'Favicon path or media reference', 'whatsapp_number' => 'WhatsApp number',
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
