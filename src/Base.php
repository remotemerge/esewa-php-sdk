<?php

namespace Cixware\Esewa;

abstract class Base
{
    // process
    abstract public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void;

    // verification
    abstract public function verify(string $referenceId, string $productId, float $amount): object;
}
