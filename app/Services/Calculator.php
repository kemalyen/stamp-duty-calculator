<?php

namespace App\Services;

abstract class Calculator
{
    /**
     * Default SDLT configuration (embedded for unit testing without Laravel)
     */
    protected array $defaultConfig = [
        'standard' => [
            ['threshold' => 125000, 'rate' => 0],
            ['threshold' => 250000, 'rate' => 2],
            ['threshold' => 925000, 'rate' => 5],
            ['threshold' => 1500000, 'rate' => 10],
            ['threshold' => PHP_FLOAT_MAX, 'rate' => 12],
        ],
        'first_time_buyer' => [
            'max_price' => 625000,
            'bands' => [
                ['threshold' => 300000, 'rate' => 0],
                ['threshold' => 625000, 'rate' => 5],
            ],
        ],
        'additional_property' => [
            'surcharge_rate' => 5,
        ],
    ];

    /**
     * Get config from Laravel or use default
     */
    protected function getConfig(): array
    {
        if (function_exists('config') && app()->bound('config')) {
            return config('sdlt');
        }
        return $this->defaultConfig;
    }


    /**
     * Calculate using standard residential rates
     */
    protected function calculateStandard(float $price, array $config): array
    {
        $bands = $config['standard'];
        return $this->calculateProgressive($price, $bands);
    }

    /**
     * Calculate using first-time buyer relief rates
     */
    protected function calculateFirstTimeBuyer(float $price, array $config): array
    {
        $bands = $config['first_time_buyer']['bands'];
        return $this->calculateProgressive($price, $bands);
    }

    /**
     * Calculate progressive band tax
     * 
     * SDLT uses progressive banding - each portion of the price
     * up to a threshold is taxed at the corresponding rate.
     */
    protected function calculateProgressive(float $price, array $bands): array
    {
        $total = 0;
        $breakdown = [];
        $remainingPrice = $price;
        $previousThreshold = 0;

        foreach ($bands as $band) {
            if ($remainingPrice <= 0) {
                break;
            }

            $bandWidth = $band['threshold'] - $previousThreshold;
            $taxableInBand = min($remainingPrice, $bandWidth);

            if ($taxableInBand > 0) {
                $bandTax = ($taxableInBand * $band['rate']) / 100;
                $total += $bandTax;

                $breakdown[] = [
                    'from' => $previousThreshold,
                    'to' => min($band['threshold'], $price),
                    'rate' => $band['rate'],
                    'taxable_amount' => $taxableInBand,
                    'tax' => $bandTax,
                ];
            }

            $remainingPrice -= $taxableInBand;
            $previousThreshold = $band['threshold'];
        }

        return [
            'total' => round($total, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Apply additional property surcharge
     */
    protected function applySurcharge(array $result, float $price, array $config): array
    {
        $surchargeRate = $config['additional_property']['surcharge_rate'];
        $surchargeAmount = ($price * $surchargeRate) / 100;

        $result['total'] = round($result['total'] + $surchargeAmount, 2);

        $result['breakdown'][] = [
            'from' => 0,
            'to' => $price,
            'rate' => $surchargeRate,
            'taxable_amount' => $price,
            'tax' => $surchargeAmount,
            'label' => 'Additional Property Surcharge',
        ];

        return $result;
    }

    /**
     * Return empty result for invalid/zero prices
     */
    protected function emptyResult(float $price): array
    {
        return [
            'total' => 0,
            'breakdown' => [],
            'effective_rate' => 0,
        ];
    }
}
