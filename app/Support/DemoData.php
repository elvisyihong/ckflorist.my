<?php

declare(strict_types=1);

namespace App\Support;

final class DemoData
{
    public static function settings(): array
    {
        return [
            'business_name' => 'CK Florist',
            'whatsapp_number' => getenv('WHATSAPP_NUMBER') ?: '6730000000',
            'telephone' => '+673 000 0000',
            'email' => 'hello@ckflorist.my',
            'address' => 'Brunei Darussalam',
            'currency' => 'BND',
            'allow_combined_enquiries' => true,
            'business_hours' => ['Monday–Saturday' => '9:00 AM–6:00 PM', 'Sunday' => 'By appointment'],
            'florist_disclaimer' => 'Sample photos are references. Flower availability and exact shades may vary, and final pricing requires confirmation.',
            'delivery_information' => 'Delivery availability and fees are confirmed after reviewing your enquiry.',
            'pickup_information' => 'Pickup time is confirmed by our team through WhatsApp.',
        ];
    }

    public static function flowers(): array
    {
        return self::records(['Rose', 'Tulip', 'Lily', 'Hydrangea', 'Baby’s Breath', 'Sunflower']);
    }

    public static function colours(): array
    {
        $names = ['Blush & Cream', 'White & Green', 'Sunset', 'Pastel Garden', 'Florist’s Choice'];
        $hexes = ['#E8C8BE', '#DDE3D5', '#D98768', '#D9CBE4', '#70866F'];
        return array_map(static fn (array $record, int $index): array => $record + ['hex_value' => $hexes[$index]], self::records($names), array_keys($names));
    }

    public static function occasions(): array { return self::records(['Birthday', 'Anniversary', 'Graduation', 'Thank You', 'Just Because']); }
    public static function sizes(): array
    {
        return [
            ['id' => 1, 'name' => 'Petite', 'slug' => 'petite', 'description' => 'A considered, compact gesture.', 'price_adjustment' => 0],
            ['id' => 2, 'name' => 'Signature', 'slug' => 'signature', 'description' => 'Our balanced everyday bouquet.', 'price_adjustment' => 25],
            ['id' => 3, 'name' => 'Statement', 'slug' => 'statement', 'description' => 'Abundant volume and premium stems.', 'price_adjustment' => 60],
        ];
    }
    public static function wrappings(): array
    {
        return [
            ['id' => 1, 'name' => 'Natural Kraft', 'slug' => 'natural-kraft', 'description' => 'Warm textured paper with a clean fold.', 'price_adjustment' => 0, 'is_florist_choice' => 0],
            ['id' => 2, 'name' => 'Soft Ivory', 'slug' => 'soft-ivory', 'description' => 'Layered matte ivory wrapping.', 'price_adjustment' => 3, 'is_florist_choice' => 0],
            ['id' => 3, 'name' => 'Florist’s Choice', 'slug' => 'florists-choice', 'description' => 'Let our florist select the best finish.', 'price_adjustment' => 0, 'is_florist_choice' => 1],
        ];
    }
    public static function decorations(): array
    {
        return [
            ['id' => 1, 'name' => 'Satin Ribbon', 'slug' => 'satin-ribbon', 'description' => 'A soft tonal ribbon finish.', 'price_adjustment' => 2],
            ['id' => 2, 'name' => 'Message Card', 'slug' => 'message-card', 'description' => 'A handwritten card with your message.', 'price_adjustment' => 1],
            ['id' => 3, 'name' => 'Fairy Lights', 'slug' => 'fairy-lights', 'description' => 'Fine warm lights for evening gifting.', 'price_adjustment' => 8],
        ];
    }
    public static function arrangements(): array { return self::records(['Hand-tied Bouquet', 'Flower Box', 'Vase Arrangement']); }

    public static function samples(): array
    {
        return [
            [
                'id' => 1, 'name' => 'Velvet Rose Study', 'slug' => 'velvet-rose-study',
                'description' => 'An abundant romantic study led by deep red and blush roses with a quiet veil of baby’s breath.',
                'cover_image' => '/public/assets/images/rose-bouquet-900.webp', 'thumbnail' => '/public/assets/images/rose-bouquet-450.webp',
                'estimated_price_min' => 88, 'estimated_price_max' => 138, 'is_featured' => 1, 'display_order' => 10,
                'created_at' => '2026-08-01 10:00:00', 'arrangement_type_id' => 1, 'arrangement_name' => 'Hand-tied Bouquet',
                'flower_ids' => [1, 5], 'flowers' => ['Rose', 'Baby’s Breath'], 'main_flower_id' => 1,
                'colour_ids' => [1], 'colours' => ['Blush & Cream'], 'occasion_ids' => [1, 2],
            ],
            [
                'id' => 2, 'name' => 'Quiet Garden', 'slug' => 'quiet-garden',
                'description' => 'A light, architectural arrangement of blush tulips, cream roses and cloud-like hydrangea.',
                'cover_image' => '/public/assets/images/pastel-bouquet-900.webp', 'thumbnail' => '/public/assets/images/pastel-bouquet-450.webp',
                'estimated_price_min' => 78, 'estimated_price_max' => 128, 'is_featured' => 1, 'display_order' => 20,
                'created_at' => '2026-08-02 10:00:00', 'arrangement_type_id' => 1, 'arrangement_name' => 'Hand-tied Bouquet',
                'flower_ids' => [2, 1, 4], 'flowers' => ['Tulip', 'Rose', 'Hydrangea'], 'main_flower_id' => 2,
                'colour_ids' => [1, 2], 'colours' => ['Blush & Cream', 'White & Green'], 'occasion_ids' => [1, 3, 4, 5],
            ],
        ];
    }

    public static function cafeCategories(): array
    {
        return [
            ['id' => 1, 'name' => 'Coffee', 'slug' => 'coffee', 'description' => 'Espresso-led classics and house signatures.'],
            ['id' => 2, 'name' => 'Tea & Botanical', 'slug' => 'tea-botanical', 'description' => 'Tea, florals and refreshing infusions.'],
            ['id' => 3, 'name' => 'Bakes', 'slug' => 'bakes', 'description' => 'Small-batch treats for the café table.'],
        ];
    }

    public static function cafeProducts(): array
    {
        return [
            ['id' => 1, 'category_id' => 1, 'category_name' => 'Coffee', 'name' => 'Rose Latte', 'slug' => 'rose-latte', 'description' => 'Espresso, fresh milk and a restrained rose finish.', 'regular_price' => 6.50, 'promotional_price' => null, 'dietary_labels' => ['Contains dairy'], 'cover_image' => '/public/assets/images/cafe-900.webp', 'is_featured' => 1, 'options' => [['id' => 1, 'option_group' => 'size', 'name' => 'Regular', 'price_adjustment' => 0], ['id' => 2, 'option_group' => 'size', 'name' => 'Large', 'price_adjustment' => 1], ['id' => 3, 'option_group' => 'temperature', 'name' => 'Hot', 'price_adjustment' => 0], ['id' => 4, 'option_group' => 'temperature', 'name' => 'Iced', 'price_adjustment' => .5]]],
            ['id' => 2, 'category_id' => 2, 'category_name' => 'Tea & Botanical', 'name' => 'Botanical Iced Tea', 'slug' => 'botanical-iced-tea', 'description' => 'A fragrant chilled infusion with citrus and garden herbs.', 'regular_price' => 5.50, 'promotional_price' => 4.80, 'dietary_labels' => ['Vegan'], 'cover_image' => '/public/assets/images/cafe-900.webp', 'is_featured' => 1, 'options' => [['id' => 5, 'option_group' => 'size', 'name' => 'Regular', 'price_adjustment' => 0], ['id' => 6, 'option_group' => 'size', 'name' => 'Large', 'price_adjustment' => 1]]],
            ['id' => 3, 'category_id' => 3, 'category_name' => 'Bakes', 'name' => 'Pistachio Financier', 'slug' => 'pistachio-financier', 'description' => 'A tender almond cake with pistachio and browned butter.', 'regular_price' => 4.20, 'promotional_price' => null, 'dietary_labels' => ['Contains nuts', 'Contains dairy', 'Contains egg'], 'cover_image' => '/public/assets/images/cafe-900.webp', 'is_featured' => 0, 'options' => []],
        ];
    }

    public static function gallery(): array
    {
        return [
            ['id' => 1, 'title' => 'Velvet Rose Study', 'caption' => 'A deep rose palette for a romantic brief.', 'path' => '/public/assets/images/rose-bouquet-900.webp', 'thumbnail' => '/public/assets/images/rose-bouquet-450.webp', 'alt_text' => 'Red and blush rose bouquet'],
            ['id' => 2, 'title' => 'Quiet Garden', 'caption' => 'Soft seasonal stems with sculptural movement.', 'path' => '/public/assets/images/pastel-bouquet-900.webp', 'thumbnail' => '/public/assets/images/pastel-bouquet-450.webp', 'alt_text' => 'Blush tulip and hydrangea bouquet'],
            ['id' => 3, 'title' => 'At the café table', 'caption' => 'Botanical drinks and small-batch bakes.', 'path' => '/public/assets/images/cafe-900.webp', 'thumbnail' => '/public/assets/images/cafe-450.webp', 'alt_text' => 'Rose latte and pistachio financier'],
        ];
    }

    private static function records(array $names): array
    {
        return array_map(static fn (string $name, int $index): array => [
            'id' => $index + 1,
            'name' => $name,
            'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', str_replace('’', '', $name)), '-')),
            'description' => '',
        ], $names, array_keys($names));
    }
}

