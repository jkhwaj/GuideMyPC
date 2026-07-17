<?php

declare(strict_types=1);

const DIAGNOSTIC_CONFIDENCE_ALGORITHM = 'evidence-v1';

/**
 * @param list<array{cause_key:string,title:string,explanation:string,minimum_evidence:int,difficulty?:string,estimated_time?:string,required_tools?:string,risk_level?:string,backup_warning?:string}> $outcomes
 * @param list<array{cause_key:string,weight:int,explanation:string}> $evidence
 * @return list<array<string,mixed>>
 */
function confidence_rank(array $outcomes, array $evidence): array
{
    $scores = [];
    foreach ($outcomes as $outcome) {
        $scores[$outcome['cause_key']] = ['outcome' => $outcome, 'raw' => 0, 'supporting' => [], 'conflicting' => []];
    }
    foreach ($evidence as $item) {
        if (!isset($scores[$item['cause_key']])) continue;
        $scores[$item['cause_key']]['raw'] += $item['weight'];
        if ($item['weight'] >= 0) {
            $scores[$item['cause_key']]['supporting'][] = $item['explanation'];
        } else {
            $scores[$item['cause_key']]['conflicting'][] = $item['explanation'];
        }
    }
    $maximum = max(1, ...array_map(static fn(array $score): int => max(0, $score['raw']), $scores));
    $ranked = [];
    foreach ($scores as $score) {
        $evidenceCount = count($score['supporting']) + count($score['conflicting']);
        $raw = $score['raw'];
        $percent = $evidenceCount < $score['outcome']['minimum_evidence'] ? null : (int) round(max(0, $raw) / $maximum * 100);
        $band = $percent === null ? 'Uncertain' : ($percent >= 70 ? 'High' : ($percent >= 40 ? 'Moderate' : 'Low'));
        $ranked[] = $score['outcome'] + ['raw_score' => $raw, 'score' => $percent, 'band' => $band, 'supporting' => $score['supporting'], 'conflicting' => $score['conflicting']];
    }
    usort($ranked, static fn(array $left, array $right): int => $right['raw_score'] <=> $left['raw_score'] ?: strcmp($left['cause_key'], $right['cause_key']));
    return $ranked;
}
