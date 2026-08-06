<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Validator;
use App\Repositories\CatalogueRepository;
use App\Repositories\EnquiryRepository;
use App\Repositories\SettingsRepository;

final class EnquiryService
{
    public function __construct(
        private readonly SelectionService $selection,
        private readonly CatalogueRepository $catalogue,
        private readonly SettingsRepository $settings,
        private readonly EnquiryRepository $enquiries,
        private readonly WhatsAppService $whatsApp,
    ) {}

    public function submit(array $input, string $ip): array
    {
        $errors = Validator::enquiry($input);
        $current = $this->selection->current();
        if (empty($current['bouquet']) && empty($current['cafe'])) {
            $errors['selection'] = 'Add a bouquet or café item before sending an enquiry.';
        }
        $settings = $this->settings->all();
        if (!empty($current['bouquet']) && !empty($current['cafe']) && empty($settings['allow_combined_enquiries'])) {
            $errors['selection'] = 'Florist and café selections cannot currently be combined.';
        }
        $errors += $this->validateSelection($current);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }
        if (!Database::available()) {
            throw new \RuntimeException('Enquiries are temporarily unavailable while the database is being configured.');
        }

        $bouquet = $this->hydrateBouquet($current['bouquet']);
        $cafe = array_values($current['cafe'] ?? []);
        $cafeTotal = array_sum(array_map(fn (array $item): float => (float) $item['unit_price'] * (int) $item['quantity'], $cafe));
        $data = [
            'customer_name' => trim((string) $input['customer_name']),
            'customer_phone' => trim((string) $input['customer_phone']),
            'customer_email' => trim((string) ($input['customer_email'] ?? '')),
            'fulfilment_method' => $input['fulfilment_method'],
            'requested_date' => $input['requested_date'],
            'requested_time' => trim((string) ($input['requested_time'] ?? '')),
            'delivery_address' => trim((string) ($input['delivery_address'] ?? '')),
            'occasion_id' => $bouquet['occasion_id'] ?? 0,
            'bouquet' => $bouquet,
            'cafe' => $cafe,
            'estimated_total_min' => $bouquet ? (float) $bouquet['budget_min'] + $cafeTotal : $cafeTotal,
            'estimated_total_max' => $bouquet ? (float) $bouquet['budget_max'] + $cafeTotal : $cafeTotal,
            'customer_notes' => trim((string) ($input['customer_notes'] ?? '')),
        ];
        $enquiry = $this->enquiries->create($data, $ip);
        $enquiry['whatsapp_url'] = $this->whatsApp->url((string) $settings['whatsapp_number'], $enquiry);
        $this->selection->clear();
        return $enquiry;
    }

    private function hydrateBouquet(?array $draft): ?array
    {
        if (!$draft) {
            return null;
        }
        $lookup = static function (array $records, int $id): ?array {
            foreach ($records as $record) {
                if ((int) $record['id'] === $id) return $record;
            }
            return null;
        };
        $names = static fn (array $records, array $ids): array => array_values(array_map(
            static fn (array $record): string => $record['name'],
            array_filter($records, static fn (array $record): bool => in_array((int) $record['id'], $ids, true))
        ));
        $sample = null;
        foreach ($this->catalogue->samples() as $candidate) {
            if ((int) $candidate['id'] === (int) $draft['sample_id']) $sample = $candidate;
        }
        $occasion = $lookup($this->catalogue->occasions(), (int) $draft['occasion_id']);
        $size = $lookup($this->catalogue->sizes(), (int) $draft['size_id']);
        $wrapping = $lookup($this->catalogue->wrappings(), (int) $draft['wrapping_id']);
        return $draft + [
            'occasion_name' => $occasion['name'] ?? '',
            'flower_names' => $names($this->catalogue->flowers(), $draft['flower_ids']),
            'sample_name' => $sample['name'] ?? '',
            'colour_names' => $names($this->catalogue->colours(), $draft['colour_ids']),
            'size_name' => $size['name'] ?? '',
            'wrapping_name' => $wrapping['name'] ?? '',
            'decoration_names' => $names($this->catalogue->decorations(), $draft['decoration_ids']),
        ];
    }

    private function validateSelection(array $selection): array
    {
        $errors = [];
        $bouquet = $selection['bouquet'] ?? null;
        if ($bouquet) {
            $validIds = static fn (array $records): array => array_map('intval', array_column($records, 'id'));
            $containsOnly = static fn (array $ids, array $valid): bool => $ids !== [] && array_diff(array_map('intval', $ids), $valid) === [];
            if (!in_array((int) ($bouquet['occasion_id'] ?? 0), $validIds($this->catalogue->occasions()), true)) $errors['occasion_id'] = 'Choose an occasion.';
            if (!$containsOnly($bouquet['flower_ids'] ?? [], $validIds($this->catalogue->flowers()))) $errors['flower_ids'] = 'Choose at least one available flower.';
            if (!$containsOnly($bouquet['colour_ids'] ?? [], $validIds($this->catalogue->colours()))) $errors['colour_ids'] = 'Choose at least one available colour.';
            if (!in_array((int) ($bouquet['size_id'] ?? 0), $validIds($this->catalogue->sizes()), true)) $errors['size_id'] = 'Choose a bouquet size.';
            if (!in_array((int) ($bouquet['wrapping_id'] ?? 0), $validIds($this->catalogue->wrappings()), true)) $errors['wrapping_id'] = 'Choose wrapping paper.';
            if (($bouquet['decoration_ids'] ?? []) !== [] && array_diff(array_map('intval', $bouquet['decoration_ids']), $validIds($this->catalogue->decorations())) !== []) $errors['decoration_ids'] = 'One or more decorations are unavailable.';
            $minimum = (float) ($bouquet['budget_min'] ?? 0); $maximum = (float) ($bouquet['budget_max'] ?? 0);
            if ($minimum < 20 || $maximum < $minimum || $maximum > 5000) $errors['budget'] = 'Enter a valid budget range between BND 20 and BND 5,000.';
            $sampleId = (int) ($bouquet['sample_id'] ?? 0);
            if ($sampleId > 0 && !in_array($sampleId, $validIds($this->catalogue->samples()), true)) $errors['sample_id'] = 'The selected inspiration sample is no longer available.';
        }
        foreach (($selection['cafe'] ?? []) as $item) {
            if (!$this->catalogue->cafeProduct((int) ($item['product_id'] ?? 0))) {
                $errors['cafe'] = 'One or more café items are no longer available.';
                break;
            }
        }
        return $errors;
    }
}
