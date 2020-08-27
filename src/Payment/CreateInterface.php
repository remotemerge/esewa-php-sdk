<?php

namespace Cixware\Esewa\Payment;

interface CreateInterface
{
    // request for payment
    public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void;
}
