<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_convert_currency_throws_on_missing_rate()
    {
        $this->expectException(\RuntimeException::class);

        // Using currencies that are unlikely to exist in the database
        convertCurrency(100, 'USD', 'XXZ');
    }

    public function test_convert_currency_returns_same_amount_for_identical_currencies()
    {
        $this->assertEquals(100.00, convertCurrency(100, 'USD', 'USD'));
    }
}
