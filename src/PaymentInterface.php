<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

interface PaymentInterface
{
    /**
     * Configures the payment parameters.
     *
     * @param array<string, mixed> $options Configuration options.
     */
    public function configure(array $options): void;

    /**
     * Gets the current environment.
     *
     * @return string The environment (test/production).
     */
    public function getEnvironment(): string;

    /**
     * Gets the product code.
     *
     * @return string The product code.
     */
    public function getProductCode(): string;

    /**
     * Verifies the payment signature.
     *
     * @param array<string, mixed> $data The data to verify.
     * @param string $signature The signature to verify against.
     * @return bool True if the signature is valid, false otherwise.
     */
    public function verifySignature(array $data, string $signature): bool;
}
