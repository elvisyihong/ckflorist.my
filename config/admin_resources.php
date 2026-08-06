<?php

declare(strict_types=1);

$basic = static fn (string $label, string $table): array => [
    'label' => $label,
    'table' => $table,
    'list' => ['name', 'slug', 'display_order', 'is_active'],
    'order' => 'display_order ASC, id DESC',
    'fields' => [
        'name' => ['label' => 'Name', 'required' => true, 'max' => 160],
        'slug' => ['label' => 'Slug', 'required' => true, 'max' => 180],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'max' => 5000],
        'display_order' => ['label' => 'Display order', 'type' => 'number'],
        'is_active' => ['label' => 'Active', 'type' => 'checkbox'],
    ],
];

return [
    'flowers' => $basic('Flower categories', 'flower_categories'),
    'arrangements' => $basic('Arrangement types', 'arrangement_types'),
    'colours' => [
        ...$basic('Colour themes', 'colour_themes'),
        'fields' => [...$basic('Colour themes', 'colour_themes')['fields'], 'hex_value' => ['label' => 'Hex colour', 'max' => 7]],
    ],
    'occasions' => $basic('Occasions', 'occasions'),
    'bouquet-sizes' => [
        ...$basic('Bouquet sizes', 'bouquet_sizes'),
        'fields' => [...$basic('Bouquet sizes', 'bouquet_sizes')['fields'], 'price_adjustment' => ['label' => 'Price adjustment', 'type' => 'number']],
    ],
    'wrapping-papers' => [
        ...$basic('Wrapping papers', 'wrapping_papers'),
        'fields' => [...$basic('Wrapping papers', 'wrapping_papers')['fields'], 'price_adjustment' => ['label' => 'Price adjustment', 'type' => 'number'], 'is_florist_choice' => ['label' => 'Florist’s choice', 'type' => 'checkbox']],
    ],
    'decorations' => [
        ...$basic('Decorations', 'decorations'),
        'fields' => [...$basic('Decorations', 'decorations')['fields'], 'price_adjustment' => ['label' => 'Price adjustment', 'type' => 'number']],
    ],
    'florist-samples' => [
        'label' => 'Florist samples', 'table' => 'florist_samples',
        'list' => ['name', 'slug', 'estimated_price_min', 'estimated_price_max', 'is_featured', 'is_active'], 'order' => 'display_order ASC, id DESC',
        'fields' => [
            'name' => ['label' => 'Name', 'required' => true, 'max' => 160], 'slug' => ['label' => 'Slug', 'required' => true, 'max' => 180],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true], 'arrangement_type_id' => ['label' => 'Arrangement type ID', 'type' => 'number'],
            'cover_image_id' => ['label' => 'Cover media ID', 'type' => 'number'], 'dominance_weight' => ['label' => 'Dominance weight', 'type' => 'number'],
            'estimated_price_min' => ['label' => 'Estimated minimum', 'type' => 'number'], 'estimated_price_max' => ['label' => 'Estimated maximum', 'type' => 'number'],
            'is_featured' => ['label' => 'Featured', 'type' => 'checkbox'], 'display_order' => ['label' => 'Display order', 'type' => 'number'], 'is_active' => ['label' => 'Active', 'type' => 'checkbox'],
        ],
    ],
    'sample-flowers' => [
        'label' => 'Sample flowers', 'table' => 'florist_sample_flowers', 'list' => ['sample_id', 'flower_category_id', 'is_main', 'dominance_weight', 'display_order'], 'order' => 'sample_id, display_order',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'flower_category_id' => ['label' => 'Flower category ID', 'type' => 'number', 'required' => true], 'is_main' => ['label' => 'Main flower', 'type' => 'checkbox'], 'dominance_weight' => ['label' => 'Dominance weight', 'type' => 'number'], 'display_order' => ['label' => 'Display order', 'type' => 'number']],
    ],
    'sample-images' => [
        'label' => 'Sample images', 'table' => 'florist_sample_images', 'list' => ['sample_id', 'media_id', 'is_cover', 'display_order'], 'order' => 'sample_id, is_cover DESC, display_order',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'media_id' => ['label' => 'Media ID', 'type' => 'number', 'required' => true], 'is_cover' => ['label' => 'Cover image', 'type' => 'checkbox'], 'display_order' => ['label' => 'Display order', 'type' => 'number']],
    ],
    'sample-colours' => [
        'label' => 'Sample colours', 'table' => 'florist_sample_colours', 'list' => ['sample_id', 'colour_theme_id'], 'order' => 'sample_id, id',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'colour_theme_id' => ['label' => 'Colour theme ID', 'type' => 'number', 'required' => true]],
    ],
    'sample-occasions' => [
        'label' => 'Sample occasions', 'table' => 'florist_sample_occasions', 'list' => ['sample_id', 'occasion_id'], 'order' => 'sample_id, id',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'occasion_id' => ['label' => 'Occasion ID', 'type' => 'number', 'required' => true]],
    ],
    'sample-wrappings' => [
        'label' => 'Sample wrapping tags', 'table' => 'florist_sample_wrappings', 'list' => ['sample_id', 'wrapping_paper_id'], 'order' => 'sample_id, id',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'wrapping_paper_id' => ['label' => 'Wrapping paper ID', 'type' => 'number', 'required' => true]],
    ],
    'sample-decorations' => [
        'label' => 'Sample decoration tags', 'table' => 'florist_sample_decorations', 'list' => ['sample_id', 'decoration_id'], 'order' => 'sample_id, id',
        'fields' => ['sample_id' => ['label' => 'Sample ID', 'type' => 'number', 'required' => true], 'decoration_id' => ['label' => 'Decoration ID', 'type' => 'number', 'required' => true]],
    ],
    'media' => [
        'label' => 'Media library', 'table' => 'media', 'list' => ['path', 'mime_type', 'width', 'height', 'alt_text'], 'order' => 'created_at DESC',
        'fields' => ['alt_text' => ['label' => 'Alternative text', 'required' => true, 'max' => 255]],
    ],
    'cafe-categories' => $basic('Café categories', 'cafe_categories'),
    'cafe-products' => [
        'label' => 'Café products', 'table' => 'cafe_products', 'list' => ['name', 'regular_price', 'promotional_price', 'is_available', 'is_featured'], 'order' => 'display_order ASC, id DESC',
        'fields' => [
            'category_id' => ['label' => 'Category ID', 'type' => 'number', 'required' => true], 'name' => ['label' => 'Name', 'required' => true, 'max' => 160],
            'slug' => ['label' => 'Slug', 'required' => true, 'max' => 180], 'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true],
            'regular_price' => ['label' => 'Regular price', 'type' => 'number', 'required' => true], 'promotional_price' => ['label' => 'Promotional price', 'type' => 'number'],
            'dietary_labels' => ['label' => 'Dietary labels JSON', 'type' => 'textarea'], 'is_available' => ['label' => 'Available', 'type' => 'checkbox'],
            'is_featured' => ['label' => 'Featured', 'type' => 'checkbox'], 'display_order' => ['label' => 'Display order', 'type' => 'number'],
        ],
    ],
    'cafe-options' => [
        'label' => 'Café product options', 'table' => 'cafe_product_options', 'list' => ['product_id', 'option_group', 'name', 'price_adjustment', 'is_available'], 'order' => 'product_id, option_group, display_order',
        'fields' => [
            'product_id' => ['label' => 'Product ID', 'type' => 'number', 'required' => true], 'option_group' => ['label' => 'Group', 'required' => true, 'max' => 20],
            'name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'price_adjustment' => ['label' => 'Price adjustment', 'type' => 'number'],
            'is_default' => ['label' => 'Default', 'type' => 'checkbox'], 'is_available' => ['label' => 'Available', 'type' => 'checkbox'], 'display_order' => ['label' => 'Display order', 'type' => 'number'],
        ],
    ],
    'banners' => [
        'label' => 'Promotional banners', 'table' => 'promotional_banners', 'list' => ['title', 'starts_at', 'ends_at', 'is_active'], 'order' => 'display_order, id DESC',
        'fields' => ['title' => ['label' => 'Title', 'required' => true], 'body' => ['label' => 'Body', 'type' => 'textarea'], 'link_url' => ['label' => 'Link URL'], 'link_label' => ['label' => 'Link label'], 'starts_at' => ['label' => 'Starts at'], 'ends_at' => ['label' => 'Ends at'], 'display_order' => ['label' => 'Display order', 'type' => 'number'], 'is_active' => ['label' => 'Active', 'type' => 'checkbox']],
    ],
    'gallery' => [
        'label' => 'Gallery', 'table' => 'gallery_items', 'list' => ['media_id', 'title', 'display_order', 'is_active'], 'order' => 'display_order, id DESC',
        'fields' => ['media_id' => ['label' => 'Media ID', 'type' => 'number', 'required' => true], 'title' => ['label' => 'Title'], 'caption' => ['label' => 'Caption', 'type' => 'textarea'], 'display_order' => ['label' => 'Display order', 'type' => 'number'], 'is_active' => ['label' => 'Active', 'type' => 'checkbox']],
    ],
    'policies' => [
        'label' => 'Policies', 'table' => 'policies', 'list' => ['title', 'slug', 'is_active', 'published_at'], 'order' => 'id DESC',
        'fields' => ['title' => ['label' => 'Title', 'required' => true], 'slug' => ['label' => 'Slug', 'required' => true], 'body' => ['label' => 'Policy text', 'type' => 'textarea', 'required' => true, 'max' => 50000], 'is_active' => ['label' => 'Active', 'type' => 'checkbox'], 'published_at' => ['label' => 'Published at']],
    ],
];
