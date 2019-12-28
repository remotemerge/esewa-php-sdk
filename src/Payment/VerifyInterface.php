<?php

namespace Cixware\Esewa\Payment;

interface VerifyInterface
{
    // request for verification
    public function verify(string $referenceId, string $productId, float $amount): object;
}
