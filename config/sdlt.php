<?php

/**
 * Stamp Duty Land Tax (SDLT) Configuration
 * 
 * Current rates for residential property in England (as of April 2026)
 * Rates must be kept in configuration - when HMRC changes rates, 
 * update this file and corresponding tests, not the calculation logic.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Standard Residential Rates
    |--------------------------------------------------------------------------
    | Progressive band structure for standard residential properties.
    | Each band specifies the upper threshold and the applicable rate.
    */
    'standard' => [
        ['threshold' => 125000, 'rate' => 0],
        ['threshold' => 250000, 'rate' => 2],
        ['threshold' => 925000, 'rate' => 5],
        ['threshold' => 1500000, 'rate' => 10],
        ['threshold' => PHP_FLOAT_MAX, 'rate' => 12],
    ],

    /*
    |--------------------------------------------------------------------------
    | First-Time Buyer Relief
    |--------------------------------------------------------------------------
    | Relief available for first-time buyers purchasing residential property
    | up to £625,000. Above this threshold, standard rates apply.
    */
    'first_time_buyer' => [
        'max_price' => 625000,
        'bands' => [
            ['threshold' => 300000, 'rate' => 0],
            ['threshold' => 625000, 'rate' => 5],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Property Surcharge
    |--------------------------------------------------------------------------
    | Applies when purchasing an additional residential property while
    | retaining ownership of another property. Added on top of standard rates.
    */
    'additional_property' => [
        'surcharge_rate' => 3,  // 3% flat surcharge
    ],
];
