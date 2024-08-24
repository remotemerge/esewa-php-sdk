<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

interface ClientInterface
{
    /**
     * Client constructor.
     */
    public function __construct(array $configs = []);

    /**
     * This method creates the form in runtime and post the data to eSewa server.
     */
    public function payment(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0.0, float $deliveryAmount = 0.0): void;

    /**
     * This method verifies the payment using the reference ID.
     */
    public function verifyPayment(string $referenceId, string $productId, float $amount): bool;
}
