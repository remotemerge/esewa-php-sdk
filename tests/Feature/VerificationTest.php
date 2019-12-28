<?php

namespace Tests\Feature;

use Tests\ParentTestCase;

class VerificationTest extends ParentTestCase
{
    public function test_with_valid_data(): void
    {
        $response = $this->client->payment->verify($_SERVER['ESEWA_REFERENCE_ID'] ?? '', $_SERVER['ESEWA_PRODUCT_ID'] ?? '', $_SERVER['ESEWA_AMOUNT'] ?? '');
        $this->assertTrue($response->verified);
    }

    public function test_with_invalid_data(): void
    {
        $response = $this->client->payment->verify('Apple', 'Google', 105);
        $this->assertFalse($response->verified);
    }
}
