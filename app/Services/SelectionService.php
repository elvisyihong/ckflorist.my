<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\CatalogueRepository;
use App\Repositories\SettingsRepository;

final class SelectionService
{
    public function __construct(private readonly CatalogueRepository $catalogue, private readonly SettingsRepository $settings) {}

    public function current(): array
    {
        $selection = Session::get('selection', ['bouquet' => null, 'cafe' => [], 'updated_at' => null]);
        return is_array($selection) ? $selection : ['bouquet' => null, 'cafe' => [], 'updated_at' => null];
    }

    public function saveBouquet(array $input): array
    {
        $selection = $this->current();
        $selection['bouquet'] = $this->normalizeBouquet($input);
        $selection['updated_at'] = time();
        Session::put('selection', $selection);
        return $selection;
    }

    public function addCafe(array $input): array
    {
        $product = $this->catalogue->cafeProduct((int) ($input['product_id'] ?? 0));
        if (!$product) {
            throw new \InvalidArgumentException('This café item is no longer available.');
        }
        $selection = $this->current();
        if (!empty($selection['bouquet']) && empty($this->settings->all()['allow_combined_enquiries'])) {
            throw new \InvalidArgumentException('Florist and café selections cannot currently be combined.');
        }
        $optionIds = array_values(array_unique(array_map('intval', $input['option_ids'] ?? [])));
        $validOptions = array_values(array_filter($product['options'], fn (array $option): bool => in_array((int) $option['id'], $optionIds, true)));
        $base = (float) ($product['promotional_price'] ?? $product['regular_price']);
        $unitPrice = $base + array_sum(array_map(fn (array $option): float => (float) $option['price_adjustment'], $validOptions));
        $quantity = max(1, min(20, (int) ($input['quantity'] ?? 1)));
        $key = hash('sha256', $product['id'] . ':' . implode(',', $optionIds) . ':' . trim((string) ($input['notes'] ?? '')));
        $selection['cafe'][$key] = [
            'key' => $key, 'product_id' => (int) $product['id'], 'name' => $product['name'],
            'options' => array_column($validOptions, 'name'), 'option_ids' => $optionIds,
            'quantity' => $quantity, 'unit_price' => $unitPrice,
            'notes' => mb_substr(trim((string) ($input['notes'] ?? '')), 0, 300),
        ];
        $selection['updated_at'] = time();
        Session::put('selection', $selection);
        return $selection;
    }

    public function removeCafe(string $key): array
    {
        $selection = $this->current();
        unset($selection['cafe'][$key]);
        $selection['updated_at'] = time();
        Session::put('selection', $selection);
        return $selection;
    }

    public function clear(): void { Session::forget('selection'); }

    private function normalizeBouquet(array $input): array
    {
        $ids = static fn (mixed $value): array => array_values(array_unique(array_map('intval', is_array($value) ? $value : [])));
        return [
            'occasion_id' => (int) ($input['occasion_id'] ?? 0),
            'flower_ids' => $ids($input['flower_ids'] ?? []),
            'sample_id' => (int) ($input['sample_id'] ?? 0),
            'colour_ids' => $ids($input['colour_ids'] ?? []),
            'size_id' => (int) ($input['size_id'] ?? 0),
            'wrapping_id' => (int) ($input['wrapping_id'] ?? 0),
            'decoration_ids' => $ids($input['decoration_ids'] ?? []),
            'budget_min' => max(0, (float) ($input['budget_min'] ?? 0)),
            'budget_max' => max(0, (float) ($input['budget_max'] ?? 0)),
            'instructions' => mb_substr(trim((string) ($input['instructions'] ?? '')), 0, 2000),
        ];
    }
}

