<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

use RemoteMerge\Esewa\Contracts\EpayInterface;
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClient;

final class Epay extends AbstractPayment implements EpayInterface
{
    /**
     * Maximum number of optional custom properties eSewa accepts per payment.
     */
    private const MAX_PROPERTIES = 3;

    /**
     * The success URL for redirecting after a successful payment.
     */
    private string $successUrl;

    /**
     * The failure URL for redirecting after a failed payment.
     */
    private string $failureUrl;

    /**
     * The HTTP client used for server-to-server requests.
     */
    private readonly HttpClientInterface $httpClient;

    public function __construct(?HttpClientInterface $httpClient = new HttpClient())
    {
        // An explicit null, as passed by EsewaFactory when no client is given, still needs a working client.
        $this->httpClient = $httpClient ?? new HttpClient();
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function configure(array $options): void
    {
        $this->validateCommonConfiguration($options);

        if (!isset($options['success_url'])) {
            throw new EsewaException('Success URL is required.');
        }

        $this->successUrl = $options['success_url'];

        if (!isset($options['failure_url'])) {
            throw new EsewaException('Failure URL is required.');
        }

        $this->failureUrl = $options['failure_url'];
    }

    /**
     * {@inheritDoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function createPayment(array $paymentData): array
    {
        $this->validateRequiredField($paymentData, 'amount', 'Amount');
        $amount = (float) $paymentData['amount'];
        $this->validateAmount($amount);
        $this->validateRequiredField($paymentData, 'transaction_uuid', 'Transaction UUID');

        $taxAmount = (float) ($paymentData['tax_amount'] ?? 0);
        $serviceCharge = (float) ($paymentData['product_service_charge'] ?? 0);
        $deliveryCharge = (float) ($paymentData['product_delivery_charge'] ?? 0);
        $totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;

        $transactionUuid = $paymentData['transaction_uuid'];
        $this->validateTransactionUuid($transactionUuid);

        $propertyFields = $this->buildPropertyFields($paymentData['properties'] ?? []);

        // Generate signature
        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $dataToSign = sprintf(
            'total_amount=%s,transaction_uuid=%s,product_code=%s',
            $totalAmount,
            $transactionUuid,
            $this->productCode
        );
        $signature = $this->generateSignature($dataToSign);

        return [
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'product_code' => $this->productCode,
            'product_service_charge' => $serviceCharge,
            'product_delivery_charge' => $deliveryCharge,
            'success_url' => $this->successUrl,
            'failure_url' => $this->failureUrl,
            'signed_field_names' => $signedFieldNames,
            'signature' => $signature,
        ] + $propertyFields;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function verifyPayment(string $encodedResponse): array
    {
        $response = $this->decodeResponse($encodedResponse);

        if (!isset($response['signature'], $response['signed_field_names'])) {
            throw new EsewaException('Invalid response: missing signature or signed fields.');
        }

        $expectedSignature = $this->generateSignature($this->buildSignedMessage($response));

        // The payload is caller-supplied, so a non-string signature must be rejected rather than reach hash_equals().
        if (!is_string($response['signature']) || !hash_equals($expectedSignature, $response['signature'])) {
            throw new EsewaException('Invalid signature in response.');
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function checkStatus(string $transactionUuid, float $totalAmount): array
    {
        $url = sprintf(
            '%s/api/epay/transaction/status/?product_code=%s&total_amount=%s&transaction_uuid=%s',
            $this->getBaseUrl('epay_status'),
            rawurlencode($this->productCode),
            rawurlencode((string) $totalAmount),
            rawurlencode($transactionUuid)
        );

        $response = $this->httpClient->get($url);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid JSON response: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new EsewaException('Invalid JSON response: expected an object.');
        }

        $this->validateResponse($data);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormActionUrl(): string
    {
        return $this->getBaseUrl('epay') . '/api/epay/main/v2/form';
    }

    /**
     * {@inheritDoc}
     */
    public function verifySignature(array $data, string $signature): bool
    {
        if (!isset($data['signed_field_names'])) {
            return false;
        }

        try {
            $message = $this->buildSignedMessage($data);
        } catch (EsewaException) {
            return false;
        }

        return hash_equals($this->generateSignature($message), $signature);
    }

    /**
     * Converts optional custom properties into eSewa's "properties[name]" form fields.
     *
     * @param mixed $properties The caller-supplied properties map.
     * @throws EsewaException If the map or any of its entries is invalid.
     * @return array<string, string> The form fields to append to the payment form.
     */
    private function buildPropertyFields(mixed $properties): array
    {
        if (!is_array($properties)) {
            throw new EsewaException('Properties must be an array.');
        }

        if (count($properties) > self::MAX_PROPERTIES) {
            throw new EsewaException(sprintf('Properties cannot contain more than %d entries.', self::MAX_PROPERTIES));
        }

        $fields = [];
        foreach ($properties as $name => $value) {
            // Brackets would change the field name eSewa parses from "properties[name]".
            if (!is_string($name) || $name === '' || strpbrk($name, '[]') !== false) {
                throw new EsewaException('Property names must be non-empty strings without square brackets.');
            }

            if (!is_string($value) && !is_int($value) && !is_float($value)) {
                throw new EsewaException('Property values must be strings or numbers: ' . $name);
            }

            $fields['properties[' . $name . ']'] = (string) $value;
        }

        return $fields;
    }

    /**
     * Builds the "field=value" message eSewa signs, joined by commas in signed_field_names order.
     *
     * @param array<string, mixed> $data The payload holding signed_field_names and each listed field.
     * @throws EsewaException If a listed field is missing from the payload.
     * @return string The message to sign.
     */
    private function buildSignedMessage(array $data): string
    {
        $pairs = [];
        foreach (explode(',', (string) $data['signed_field_names']) as $signedField) {
            if (!isset($data[$signedField])) {
                throw new EsewaException('Missing signed field: ' . $signedField);
            }

            $pairs[] = $signedField . '=' . $data[$signedField];
        }

        return implode(',', $pairs);
    }
}
