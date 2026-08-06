<?php

declare(strict_types=1);

namespace App\Services;

final class WhatsAppService
{
    public function url(string $number, array $enquiry): string
    {
        $number = preg_replace('/\D+/', '', $number) ?: '';
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($this->message($enquiry));
    }

    public function message(array $enquiry): string
    {
        $lines = [
            'CK Florist Enquiry ' . $enquiry['reference'],
            '',
            'Customer: ' . $enquiry['customer_name'],
            'Phone: ' . $enquiry['customer_phone'],
            'Fulfilment: ' . ucfirst($enquiry['fulfilment_method']),
            'Requested: ' . $enquiry['requested_date'] . ($enquiry['requested_time'] ? ' · ' . $enquiry['requested_time'] : ''),
        ];
        if (!empty($enquiry['delivery_address'])) {
            $lines[] = 'Address: ' . $enquiry['delivery_address'];
        }
        if (!empty($enquiry['bouquet'])) {
            $bouquet = $enquiry['bouquet'];
            $lines[] = '';
            $lines[] = 'Bouquet';
            $lines[] = 'Occasion: ' . ($bouquet['occasion_name'] ?: 'Not specified');
            $lines[] = 'Flowers: ' . implode(', ', $bouquet['flower_names']);
            $lines[] = 'Inspiration: ' . ($bouquet['sample_name'] ?: 'Florist’s recommendation');
            $lines[] = 'Colours: ' . implode(', ', $bouquet['colour_names']);
            $lines[] = 'Size: ' . ($bouquet['size_name'] ?: 'Not specified');
            $lines[] = 'Wrapping: ' . ($bouquet['wrapping_name'] ?: 'Not specified');
            $lines[] = 'Decorations: ' . ($bouquet['decoration_names'] ? implode(', ', $bouquet['decoration_names']) : 'None');
            $lines[] = 'Budget: BND ' . number_format((float) $bouquet['budget_min'], 2) . '–' . number_format((float) $bouquet['budget_max'], 2);
            if ($bouquet['instructions']) {
                $lines[] = 'Instructions: ' . $bouquet['instructions'];
            }
        }
        if (!empty($enquiry['cafe'])) {
            $lines[] = '';
            $lines[] = 'Café selection';
            foreach ($enquiry['cafe'] as $item) {
                $options = $item['options'] ? ' (' . implode(', ', $item['options']) . ')' : '';
                $lines[] = $item['quantity'] . ' × ' . $item['name'] . $options;
            }
        }
        if (!empty($enquiry['customer_notes'])) {
            $lines[] = '';
            $lines[] = 'Customer notes: ' . $enquiry['customer_notes'];
        }
        $lines[] = '';
        $lines[] = 'I understand that sending this enquiry does not confirm an order. Availability, final design, fulfilment and price still require CK Florist confirmation.';
        return implode("\n", $lines);
    }
}
