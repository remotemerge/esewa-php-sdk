<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\ParentTestCase;

final class VerificationTest extends ParentTestCase
{
    /**
     * @throws Exception
     */
    public function test_with_invalid_data(): void
    {
        $response = $this->esewa->verify('Apple', 'Google', 105);
        self::assertFalse($response);
    }

    /**
     * @throws Exception
     */
    public function test_with_valid_data(): void
    {
        // read values
        $referenceId = $_ENV['ESEWA_REFERENCE_ID'];
        $productId = $_ENV['ESEWA_PRODUCT_ID'];
        $esewaAmount = (float) $_ENV['ESEWA_PAID_AMOUNT'];

        $response = $this->esewa->verify($referenceId, $productId, $esewaAmount);
        self::assertTrue($response);
    }
}
