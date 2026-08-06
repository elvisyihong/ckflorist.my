<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$tests = [];
$failures = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[] = [$name, $callback];
}

function assert_true(bool $condition, string $message = 'Expected condition to be true.'): void
{
    if (!$condition) throw new RuntimeException($message);
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message ?: 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

foreach (glob(__DIR__ . '/Unit/*Test.php') ?: [] as $file) require $file;
foreach (glob(__DIR__ . '/Integration/*Test.php') ?: [] as $file) require $file;

foreach ($tests as [$name, $callback]) {
    try {
        $callback();
        fwrite(STDOUT, "PASS {$name}\n");
    } catch (Throwable $exception) {
        $failures[] = [$name, $exception->getMessage()];
        fwrite(STDOUT, "FAIL {$name}: {$exception->getMessage()}\n");
    }
}

fwrite(STDOUT, sprintf("\n%d tests, %d failures\n", count($tests), count($failures)));
exit($failures === [] ? 0 : 1);

