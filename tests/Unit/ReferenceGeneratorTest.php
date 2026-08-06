<?php

use App\Services\ReferenceGenerator;

test('enquiry reference follows required format and date', function (): void {
    $reference = (new ReferenceGenerator())->generate(static fn (): bool => false, new DateTimeImmutable('2026-08-06', new DateTimeZone('UTC')));
    assert_true((bool) preg_match('/^CKF-20260806-[A-F0-9]{5}$/', $reference), $reference);
});

test('enquiry reference checks uniqueness before returning', function (): void {
    $checks = 0;
    (new ReferenceGenerator())->generate(static function () use (&$checks): bool { return ++$checks === 1; });
    assert_same(2, $checks);
});

