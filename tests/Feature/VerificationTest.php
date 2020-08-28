<?php

namespace Tests\Feature;

use Tests\ParentTestCase;

class VerificationTest extends ParentTestCase
{
    public function test_with_invalid_data(): void
    {
        $response = $this->esewa->verify('Apple', 'Google', 105);
        self::assertFalse($response->verified);
    }

    public function test_with_valid_data(): void
    {
        $referenceId = getenv('ESEWA_REFERENCE_ID');
        $productId = getenv('ESEWA_PRODUCT_ID');
        $esewaAmount = getenv('ESEWA_PAID_AMOUNT');

        $response = $this->esewa->verify($referenceId, $productId, $esewaAmount);
        self::assertTrue($response->verified);
    }
}
