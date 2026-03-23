<?php

namespace App\Services;

class PriorityMatrixService
{
    private const MATRIX = [
        'critical' => [
            'extensive' => 'critical',
            'significant' => 'critical',
            'moderate' => 'high',
            'minor' => 'high',
        ],
        'high' => [
            'extensive' => 'critical',
            'significant' => 'high',
            'moderate' => 'high',
            'minor' => 'medium',
        ],
        'medium' => [
            'extensive' => 'high',
            'significant' => 'high',
            'moderate' => 'medium',
            'minor' => 'medium',
        ],
        'low' => [
            'extensive' => 'high',
            'significant' => 'medium',
            'moderate' => 'medium',
            'minor' => 'low',
        ],
    ];

    public function calculate(string $urgency, string $impact): string
    {
        return self::MATRIX[$urgency][$impact] ?? 'medium';
    }
}
