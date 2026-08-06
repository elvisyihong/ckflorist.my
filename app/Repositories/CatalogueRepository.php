<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Support\DemoData;
use PDO;

final class CatalogueRepository
{
    public function samples(array $filters = []): array
    {
        if (!Database::available()) {
            $samples = DemoData::samples();
            if (!empty($filters['flower'])) {
                $samples = array_values(array_filter($samples, fn (array $sample): bool => in_array((int) $filters['flower'], $sample['flower_ids'], true)));
            }
            return $samples;
        }

        $sql = "SELECT s.*, a.name arrangement_name, m.path cover_image, m.thumbnail_path thumbnail
                FROM florist_samples s
                LEFT JOIN arrangement_types a ON a.id = s.arrangement_type_id
                LEFT JOIN media m ON m.id = s.cover_image_id
                WHERE s.is_active = 1";
        $params = [];
        if (!empty($filters['flower'])) {
            $sql .= ' AND EXISTS (SELECT 1 FROM florist_sample_flowers sf WHERE sf.sample_id = s.id AND sf.flower_category_id = :flower)';
            $params['flower'] = (int) $filters['flower'];
        }
        $sql .= ' ORDER BY s.is_featured DESC, s.display_order ASC, s.created_at DESC LIMIT 100';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        $samples = $statement->fetchAll();
        return $this->hydrateSamples($samples);
    }

    public function sampleBySlug(string $slug): ?array
    {
        foreach ($this->samples() as $sample) {
            if ($sample['slug'] === $slug) {
                return $sample;
            }
        }
        return null;
    }

    public function flowers(): array { return $this->simple('flower_categories', DemoData::flowers()); }
    public function colours(): array { return $this->simple('colour_themes', DemoData::colours(), 'id, name, slug, hex_value, description'); }
    public function occasions(): array { return $this->simple('occasions', DemoData::occasions()); }
    public function arrangements(): array { return $this->simple('arrangement_types', DemoData::arrangements()); }
    public function sizes(): array { return $this->simple('bouquet_sizes', DemoData::sizes(), 'id, name, slug, description, price_adjustment'); }
    public function wrappings(): array { return $this->simple('wrapping_papers', DemoData::wrappings(), 'id, name, slug, description, price_adjustment, is_florist_choice'); }
    public function decorations(): array { return $this->simple('decorations', DemoData::decorations(), 'id, name, slug, description, price_adjustment'); }

    public function cafeCategories(): array { return $this->simple('cafe_categories', DemoData::cafeCategories()); }

    public function promotionalBanners(): array
    {
        if (!Database::available()) {
            return [];
        }

        return Database::connection()->query(
            "SELECT b.*, CONCAT('/', m.path) image
             FROM promotional_banners b
             LEFT JOIN media m ON m.id = b.image_id
             WHERE b.is_active = 1
               AND (b.starts_at IS NULL OR b.starts_at <= UTC_TIMESTAMP())
               AND (b.ends_at IS NULL OR b.ends_at >= UTC_TIMESTAMP())
             ORDER BY b.display_order, b.created_at DESC
             LIMIT 8"
        )->fetchAll();
    }

    public function cafeProducts(): array
    {
        if (!Database::available()) {
            return DemoData::cafeProducts();
        }
        $rows = Database::connection()->query("SELECT p.*, c.name category_name, c.slug category_slug, m.path cover_image FROM cafe_products p JOIN cafe_categories c ON c.id = p.category_id LEFT JOIN media m ON m.id = p.cover_image_id WHERE p.is_available = 1 AND c.is_active = 1 ORDER BY c.display_order, p.is_featured DESC, p.display_order")->fetchAll();
        $optionStatement = Database::connection()->prepare('SELECT id, option_group, name, price_adjustment, is_default FROM cafe_product_options WHERE product_id = :id AND is_available = 1 ORDER BY option_group, display_order');
        foreach ($rows as &$row) {
            $row['dietary_labels'] = json_decode((string) ($row['dietary_labels'] ?? '[]'), true) ?: [];
            $row['cover_image'] = $row['cover_image'] ? '/' . ltrim($row['cover_image'], '/') : '/public/assets/images/cafe-900.webp';
            $optionStatement->execute(['id' => $row['id']]);
            $row['options'] = $optionStatement->fetchAll();
        }
        return $rows;
    }

    public function cafeProduct(int $id): ?array
    {
        foreach ($this->cafeProducts() as $product) {
            if ((int) $product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    public function gallery(): array
    {
        if (!Database::available()) {
            return DemoData::gallery();
        }
        return Database::connection()->query("SELECT g.id, g.title, g.caption, CONCAT('/', m.path) path, CONCAT('/', COALESCE(m.thumbnail_path, m.path)) thumbnail, m.alt_text FROM gallery_items g JOIN media m ON m.id = g.media_id WHERE g.is_active = 1 ORDER BY g.display_order, g.created_at DESC LIMIT 60")->fetchAll();
    }

    private function simple(string $table, array $fallback, string $columns = 'id, name, slug, description'): array
    {
        if (!Database::available()) {
            return $fallback;
        }
        return Database::connection()->query("SELECT {$columns} FROM {$table} WHERE is_active = 1 ORDER BY display_order, name")->fetchAll();
    }

    private function hydrateSamples(array $samples): array
    {
        $pdo = Database::connection();
        $flowers = $pdo->prepare('SELECT f.id, f.name, sf.is_main FROM florist_sample_flowers sf JOIN flower_categories f ON f.id = sf.flower_category_id WHERE sf.sample_id = :id ORDER BY sf.is_main DESC, sf.display_order');
        $colours = $pdo->prepare('SELECT c.id, c.name FROM florist_sample_colours sc JOIN colour_themes c ON c.id = sc.colour_theme_id WHERE sc.sample_id = :id');
        $occasions = $pdo->prepare('SELECT o.id FROM florist_sample_occasions so JOIN occasions o ON o.id = so.occasion_id WHERE so.sample_id = :id');
        foreach ($samples as &$sample) {
            $flowers->execute(['id' => $sample['id']]);
            $flowerRows = $flowers->fetchAll();
            $sample['flower_ids'] = array_map('intval', array_column($flowerRows, 'id'));
            $sample['flowers'] = array_column($flowerRows, 'name');
            $main = array_values(array_filter($flowerRows, fn (array $row): bool => (bool) $row['is_main']));
            $sample['main_flower_id'] = isset($main[0]) ? (int) $main[0]['id'] : null;
            $colours->execute(['id' => $sample['id']]);
            $colourRows = $colours->fetchAll();
            $sample['colour_ids'] = array_map('intval', array_column($colourRows, 'id'));
            $sample['colours'] = array_column($colourRows, 'name');
            $occasions->execute(['id' => $sample['id']]);
            $sample['occasion_ids'] = array_map('intval', array_column($occasions->fetchAll(), 'id'));
            $sample['cover_image'] = $sample['cover_image'] ? '/' . ltrim($sample['cover_image'], '/') : '/public/assets/images/pastel-bouquet-900.webp';
            $sample['thumbnail'] = $sample['thumbnail'] ? '/' . ltrim($sample['thumbnail'], '/') : $sample['cover_image'];
        }
        return $samples;
    }
}
