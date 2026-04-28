<?php

namespace App\Services;

/**
 * Stamp Duty Land Tax Calculator
 * 
 * Pure calculation service - same inputs produce same outputs.
 * No database queries, no side effects, no external calls.
 * 
 * Rate configuration is loaded from config/sdlt.php or can be injected.
 */
class StampDutyCalculator extends Calculator
{
    /**
     * Calculate SDLT for a property purchase
     *
     * @param float $propertyPrice Purchase price in pounds
     * @param bool $isFirstTimeBuyer Whether the buyer is a first-time buyer
     * @param bool $isAdditionalProperty Whether this is an additional property
     * @param array|null $config Optional config array (for testing)
     * @return array Calculation result with total, breakdown, and effective rate
     */
    public function calculate(float $propertyPrice, bool $isFirstTimeBuyer = false, bool $isAdditionalProperty = false, ?array $config = null): array
    {
        // Handle edge cases
        if ($propertyPrice <= 0) {
            return $this->emptyResult($propertyPrice);
        }

        // Use provided config, Laravel config, or default
        $config = $config ?? $this->getConfig();

        // Determine which rate structure to use
        if ($isFirstTimeBuyer && $propertyPrice <= $config['first_time_buyer']['max_price']) {
            $result = $this->calculateFirstTimeBuyer($propertyPrice, $config);
        } else {
            $result = $this->calculateStandard($propertyPrice, $config);
        }

        // Apply additional property surcharge if applicable
        if ($isAdditionalProperty) {
            $result = $this->applySurcharge($result, $propertyPrice, $config);
        }

        // Calculate effective rate
        $result['effective_rate'] = $propertyPrice > 0
            ? round(($result['total'] / $propertyPrice) * 100, 2)
            : 0;

        return $result;
    }
}
