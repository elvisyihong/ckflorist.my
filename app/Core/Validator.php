<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function enquiry(array $input): array
    {
        $errors = [];
        self::requiredString($input, 'customer_name', 'Name', 2, 160, $errors);
        self::requiredString($input, 'customer_phone', 'Phone number', 7, 40, $errors);
        self::requiredString($input, 'fulfilment_method', 'Fulfilment method', 1, 20, $errors);
        self::requiredString($input, 'requested_date', 'Requested date', 10, 10, $errors);

        if (!in_array($input['fulfilment_method'] ?? null, ['delivery', 'pickup'], true)) {
            $errors['fulfilment_method'] = 'Choose delivery or pickup.';
        }
        if (!preg_match('/^[+0-9][0-9\s().-]{6,39}$/', (string) ($input['customer_phone'] ?? ''))) {
            $errors['customer_phone'] = 'Enter a valid phone or WhatsApp number.';
        }
        if (($input['fulfilment_method'] ?? null) === 'delivery' && trim((string) ($input['delivery_address'] ?? '')) === '') {
            $errors['delivery_address'] = 'Enter the delivery address.';
        }
        if (!empty($input['customer_email']) && !filter_var($input['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Enter a valid email address.';
        }
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', (string) ($input['requested_date'] ?? ''));
        $today = new \DateTimeImmutable('today');
        if (!$date || $date->format('Y-m-d') !== ($input['requested_date'] ?? '') || $date < $today) {
            $errors['requested_date'] = 'Choose today or a future date.';
        }
        if (empty($input['consent'])) {
            $errors['consent'] = 'Confirm that you understand this is an enquiry, not an order confirmation.';
        }
        if (mb_strlen((string) ($input['customer_notes'] ?? '')) > 2000) {
            $errors['customer_notes'] = 'Keep notes under 2,000 characters.';
        }
        return $errors;
    }

    public static function login(array $input): array
    {
        $errors = [];
        if (!filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (!is_string($input['password'] ?? null) || $input['password'] === '') {
            $errors['password'] = 'Enter your password.';
        }
        return $errors;
    }

    private static function requiredString(array $input, string $key, string $label, int $min, int $max, array &$errors): void
    {
        $value = trim((string) ($input[$key] ?? ''));
        $length = mb_strlen($value);
        if ($length < $min) {
            $errors[$key] = "{$label} is required.";
        } elseif ($length > $max) {
            $errors[$key] = "{$label} is too long.";
        }
    }
}
