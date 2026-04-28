<?php

namespace Tests\Unit;

use App\Services\StampDutyCalculator;
use PHPUnit\Framework\TestCase;

class StampDutyCalculatorTest extends TestCase
{
    private StampDutyCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new StampDutyCalculator();
    }

    // ============================================
    // STANDARD RATES TESTS
    // ============================================

    public function test_standard_rate_nil_band(): void
    {
        // Property under £125,000 - nil rate
        $result = $this->calculator->calculate(100000, false, false);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['effective_rate']);
    }

    public function test_standard_rate_first_band(): void
    {
        // Property between £125,001 and £250,000 at 2%
        $result = $this->calculator->calculate(200000, false, false);

        // £200,000 - £125,000 = £75,000 at 2% = £1,500
        $this->assertEquals(1500, $result['total']);
        $this->assertEquals(0.75, $result['effective_rate']);
    }

    public function test_standard_rate_second_band(): void
    {
        // Property between £250,001 and £925,000 at 5%
        $result = $this->calculator->calculate(500000, false, false);

        // First £125,000 at 0% = £0
        // Next £125,000 at 2% = £2,500
        // Next £250,000 at 5% = £12,500
        // Total = £15,000
        $this->assertEquals(15000, $result['total']);
        $this->assertEquals(3, $result['effective_rate']);
    }

    public function test_standard_rate_third_band(): void
    {
        // Property between £925,001 and £1.5M at 10%
        $result = $this->calculator->calculate(1000000, false, false);

        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        // £675,000 at 5% = £33,750
        // £75,000 at 10% = £7,500
        // Total = £43,750
        $this->assertEquals(43750, $result['total']);
        $this->assertEquals(4.38, $result['effective_rate']);
    }

    public function test_standard_rate_top_band(): void
    {
        // Property over £1.5M at 12%
        $result = $this->calculator->calculate(2000000, false, false);

        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        // £675,000 at 5% = £33,750
        // £575,000 at 10% = £57,500
        // £500,000 at 12% = £60,000
        // Total = £153,750
        $this->assertEquals(153750, $result['total']);
        // Exactly at first threshold - nil rate
        $result = $this->calculator->calculate(125000, false, false);

        $this->assertEquals(0, $result['total']);
    }

    public function test_boundary_at_250000(): void
    {
        // Exactly at second threshold
        $result = $this->calculator->calculate(250000, false, false);

        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        $this->assertEquals(2500, $result['total']);
    }

    public function test_boundary_at_925000(): void
    {
        // Exactly at third threshold
        $result = $this->calculator->calculate(925000, false, false);

        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        // £675,000 at 5% = £33,750
        $this->assertEquals(36250, $result['total']);
    }

    public function test_boundary_at_1500000(): void
    {
        // Exactly at fourth threshold
        $result = $this->calculator->calculate(1500000, false, false);

        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        // £675,000 at 5% = £33,750
        // £575,000 at 10% = £57,500
        $this->assertEquals(93750, $result['total']);
    }

    // ============================================
    // FIRST-TIME BUYER RELIEF TESTS
    // ============================================

    public function test_first_time_buyer_nil_band(): void
    {
        // Under £300,000 - nil rate
        $result = $this->calculator->calculate(250000, true, false);

        $this->assertEquals(0, $result['total']);
    }

    public function test_first_time_buyer_first_band(): void
    {
        // Between £300,001 and £625,000 at 5%
        $result = $this->calculator->calculate(400000, true, false);

        // £400,000 - £300,000 = £100,000 at 5% = £5,000
        $this->assertEquals(5000, $result['total']);
    }

    public function test_first_time_buyer_at_max(): void
    {
        // At £625,000 - max for first-time buyer relief
        $result = $this->calculator->calculate(625000, true, false);

        // £625,000 - £300,000 = £325,000 at 5% = £16,250
        $this->assertEquals(16250, $result['total']);
    }

    public function test_first_time_buyer_above_max(): void
    {
        // Above £625,000 - standard rates apply
        $result = $this->calculator->calculate(700000, true, false);

        // Standard rates apply:
        // £125,000 at 0% = £0
        // £125,000 at 2% = £2,500
        // £450,000 at 5% = £22,500
        // Total = £25,000
        $this->assertEquals(25000, $result['total']);
    }

    // ============================================
    // ADDITIONAL PROPERTY SURCHARGE TESTS
    // ============================================

    public function test_additional_property_surcharge(): void
    {
        // 3% surcharge on additional property
        $result = $this->calculator->calculate(200000, false, true);

        // Standard: £75,000 at 2% = £1,500
        // Plus 3% surcharge on full price: £200,000 × 3% = £6,000
        // Total = £7,500
        $this->assertEquals(7500, $result['total']);
    }

    public function test_additional_property_with_first_time_buyer(): void
    {
        // First-time buyer relief + additional property surcharge
        $result = $this->calculator->calculate(400000, true, true);

        // First-time buyer: £100,000 at 5% = £5,000
        // Plus 3% surcharge: £400,000 × 3% = £12,000
        // Total = £17,000
        $this->assertEquals(17000, $result['total']);
    }

    // ============================================
    // EDGE CASE TESTS
    // ============================================

    public function test_zero_price(): void
    {
        $result = $this->calculator->calculate(0, false, false);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['effective_rate']);
    }

    public function test_negative_price(): void
    {
        $result = $this->calculator->calculate(-100000, false, false);

        $this->assertEquals(0, $result['total']);
    }

    public function test_very_low_price(): void
    {
        // Just above nil band threshold
        $result = $this->calculator->calculate(125001, false, false);

        // £1 at 2% = £0.02
        $this->assertEquals(0.02, $result['total']);
    }

    // ============================================
    // BREAKDOWN VERIFICATION
    // ============================================

    public function test_breakdown_structure(): void
    {
        $result = $this->calculator->calculate(500000, false, false);

        $this->assertCount(3, $result['breakdown']);

        // Verify first band
        $this->assertEquals(0, $result['breakdown'][0]['rate']);
        $this->assertEquals(125000, $result['breakdown'][0]['taxable_amount']);

        // Verify second band
        $this->assertEquals(2, $result['breakdown'][1]['rate']);
        $this->assertEquals(125000, $result['breakdown'][1]['taxable_amount']);

        // Verify third band
        $this->assertEquals(5, $result['breakdown'][2]['rate']);
        $this->assertEquals(250000, $result['breakdown'][2]['taxable_amount']);
    }

    public function test_breakdown_sums_to_total(): void
    {
        $result = $this->calculator->calculate(500000, false, false);

        $sum = array_sum(array_column($result['breakdown'], 'tax'));
        $this->assertEquals($result['total'], $sum);
    }
}
