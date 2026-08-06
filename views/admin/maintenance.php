<?php $menuIds = array_column($images, 'id'); ?>
<header class="admin-page-head">
    <div><p class="eyebrow">Public access control</p><h1>Maintenance mode</h1></div>
    <span class="status-pill <?= !empty($settings['maintenance_mode']) ? 'is-on' : '' ?>"><?= !empty($settings['maintenance_mode']) ? 'On' : 'Off' ?></span>
</header>

<form class="admin-form" method="post" action="/admin/maintenance" data-maintenance-form>
    <?= csrf_field() ?>
    <section class="admin-panel maintenance-control-panel">
        <div>
            <p class="eyebrow">Visibility</p>
            <h2>Show only your uploaded menu</h2>
            <p>When enabled, every public website route shows the ordered menu images below. Admin login and the full admin panel remain available.</p>
        </div>
        <label class="toggle maintenance-toggle">
            <input type="checkbox" name="maintenance_mode" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
            <i></i><span>Maintenance mode</span>
        </label>
    </section>

    <section class="admin-panel form-fields">
        <label class="field">
            <span>Page title</span>
            <input name="maintenance_title" maxlength="120" required value="<?= e($settings['maintenance_title'] ?? 'Our menu is available') ?>">
        </label>
        <label class="field field-full">
            <span>Short message</span>
            <textarea name="maintenance_message" maxlength="500"><?= e($settings['maintenance_message'] ?? 'Browse our current menu while we prepare the full website.') ?></textarea>
        </label>
    </section>

    <section class="admin-panel">
        <div class="maintenance-upload-head">
            <div><p class="eyebrow">Menu images</p><h2>Upload and arrange menu pages</h2><p>JPG, PNG, WebP or AVIF, up to 5 MB each. Images are converted to WebP automatically.</p></div>
            <label class="button button-small" data-maintenance-upload-label>
                Upload images
                <input class="sr-only" type="file" accept="image/jpeg,image/png,image/webp,image/avif" multiple data-maintenance-upload>
            </label>
        </div>
        <p class="upload-status" aria-live="polite" data-maintenance-upload-status></p>
        <input type="hidden" name="maintenance_menu_images" value="<?= e(json_encode($menuIds, JSON_THROW_ON_ERROR)) ?>" data-maintenance-image-ids>
        <p class="empty-state compact" <?= $images !== [] ? 'hidden' : '' ?> data-maintenance-empty>Upload at least one menu image before turning maintenance mode on.</p>
        <div class="maintenance-image-grid" data-maintenance-image-list>
            <?php foreach ($images as $index => $image): ?>
                <article class="maintenance-image-item" data-maintenance-image data-media-id="<?= (int) $image['id'] ?>">
                    <img src="<?= e($image['thumbnail']) ?>" alt="<?= e($image['alt_text'] ?: 'Menu image ' . ($index + 1)) ?>">
                    <div><strong>Menu page <span data-maintenance-position><?= $index + 1 ?></span></strong><small><?= e($image['alt_text'] ?: 'Uploaded menu image') ?></small></div>
                    <div class="maintenance-image-actions">
                        <button class="button button-small button-outline" type="button" data-menu-up aria-label="Move menu page up">↑</button>
                        <button class="button button-small button-outline" type="button" data-menu-down aria-label="Move menu page down">↓</button>
                        <button class="button button-small button-outline" type="button" data-menu-remove>Remove</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="field-help">Removing an image here only removes it from maintenance mode; it remains available in the Media library.</p>
    </section>

    <footer class="admin-save-bar"><button class="button" type="submit">Save maintenance settings</button></footer>
</form>
