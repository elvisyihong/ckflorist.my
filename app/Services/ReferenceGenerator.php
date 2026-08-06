<?php

declare(strict_types=1);

namespace App\Services;

final class ReferenceGenerator
{
    public function generate(callable $exists, ?\DateTimeInterface $date = null): string
    {
        $date ??= new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        do {
            $reference = 'CKF-' . $date->format('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));
        } while ($exists($reference));
        return $reference;
    }
}
