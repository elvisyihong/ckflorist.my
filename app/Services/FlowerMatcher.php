<?php

declare(strict_types=1);

namespace App\Services;

final class FlowerMatcher
{
    public function rank(array $samples, array $selectedFlowerIds, array $preferences = []): array
    {
        $selected = array_values(array_unique(array_map('intval', array_filter($selectedFlowerIds, 'is_numeric'))));
        if ($selected === []) {
            return [];
        }
        $primary = $selected[0];
        $ranked = [];

        foreach ($samples as $sample) {
            $flowers = array_values(array_unique(array_map('intval', $sample['flower_ids'] ?? [])));
            $found = array_values(array_intersect($selected, $flowers));
            if ($found === []) {
                continue;
            }
            $missing = count(array_diff($selected, $flowers));
            $additional = count(array_diff($flowers, $selected));
            $allSelectedPresent = $missing === 0;
            $exact = $allSelectedPresent && $additional === 0;
            $score = count($found) * 100;
            $score += $allSelectedPresent ? 80 : 0;
            $score += $exact ? 50 : 0;
            $score += ((int) ($sample['main_flower_id'] ?? 0) === $primary) ? 30 : 0;
            $score += $this->matchesPreference($sample['colour_ids'] ?? [], $preferences['colour_ids'] ?? []) ? 20 : 0;
            $score += !empty($preferences['arrangement_type_id']) && (int) ($sample['arrangement_type_id'] ?? 0) === (int) $preferences['arrangement_type_id'] ? 15 : 0;
            $score += !empty($preferences['occasion_id']) && in_array((int) $preferences['occasion_id'], array_map('intval', $sample['occasion_ids'] ?? []), true) ? 10 : 0;
            $score -= $additional * 15;
            $score -= $missing * 25;
            $sample['match_score'] = $score;
            $sample['match_complete'] = $allSelectedPresent;
            $sample['match_exact'] = $exact;
            $sample['matched_flower_count'] = count($found);
            $ranked[] = $sample;
        }

        usort($ranked, static function (array $a, array $b): int {
            return ($b['match_score'] <=> $a['match_score'])
                ?: ((int) $b['is_featured'] <=> (int) $a['is_featured'])
                ?: ((int) $a['display_order'] <=> (int) $b['display_order'])
                ?: strcmp((string) $b['created_at'], (string) $a['created_at']);
        });
        return $ranked;
    }

    private function matchesPreference(array $sample, array $selected): bool
    {
        return $selected !== [] && array_intersect(array_map('intval', $sample), array_map('intval', $selected)) !== [];
    }
}

