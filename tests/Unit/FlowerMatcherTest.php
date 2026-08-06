<?php

use App\Services\FlowerMatcher;

$sample = static fn (int $id, array $flowers, int $main, int $featured = 0, int $order = 10, string $created = '2026-01-01'): array => [
    'id' => $id, 'flower_ids' => $flowers, 'main_flower_id' => $main, 'colour_ids' => [1],
    'arrangement_type_id' => 1, 'occasion_ids' => [1], 'is_featured' => $featured,
    'display_order' => $order, 'created_at' => $created,
];

test('exact flower combination ranks above complete combination with extras', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([$sample(1, [1,2], 1), $sample(2, [1,2,3], 1)], [1,2]);
    assert_same([1,2], array_column($ranked, 'id'));
    assert_true($ranked[0]['match_exact']);
    assert_true($ranked[0]['match_score'] > $ranked[1]['match_score']);
});

test('samples containing every flower rank above partial matches', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([$sample(1, [1], 1), $sample(2, [1,2,3], 2)], [1,2]);
    assert_same(2, $ranked[0]['id']);
    assert_true($ranked[0]['match_complete']);
    assert_true(!$ranked[1]['match_complete']);
});

test('primary flower receives main-flower bonus', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([$sample(1, [1,2,3], 2), $sample(2, [1,2,4], 1)], [1,2]);
    assert_same(2, $ranked[0]['id']);
    assert_same(30, $ranked[0]['match_score'] - $ranked[1]['match_score']);
});

test('additional unselected flowers receive fifteen point penalty each', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([$sample(1, [1,2,3], 1), $sample(2, [1,2,3,4,5], 1)], [1,2]);
    assert_same(30, $ranked[0]['match_score'] - $ranked[1]['match_score']);
});

test('samples with no selected flowers are excluded', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([$sample(1, [4,5], 4), $sample(2, [1,3], 1)], [1,2]);
    assert_same([2], array_column($ranked, 'id'));
});

test('colour arrangement and occasion preferences add suggested weights', function () use ($sample): void {
    $preferred = $sample(1, [1], 1); $other = $sample(2, [1], 1);
    $preferred['colour_ids'] = [7]; $preferred['arrangement_type_id'] = 8; $preferred['occasion_ids'] = [9];
    $ranked = (new FlowerMatcher())->rank([$other, $preferred], [1], ['colour_ids' => [7], 'arrangement_type_id' => 8, 'occasion_id' => 9]);
    assert_same(1, $ranked[0]['id']);
    assert_same(45, $ranked[0]['match_score'] - $ranked[1]['match_score']);
});

test('ties use featured then display order then newest date', function () use ($sample): void {
    $ranked = (new FlowerMatcher())->rank([
        $sample(1, [1], 1, 0, 5, '2026-05-01'),
        $sample(2, [1], 1, 1, 50, '2026-01-01'),
        $sample(3, [1], 1, 0, 5, '2026-06-01'),
        $sample(4, [1], 1, 0, 1, '2026-01-01'),
    ], [1]);
    assert_same([2,4,3,1], array_column($ranked, 'id'));
});

