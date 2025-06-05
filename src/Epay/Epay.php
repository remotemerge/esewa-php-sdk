<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Epay;

use RemoteMerge\Esewa\AbstractPayment;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClientInterface;
use RemoteMerge\Esewa\Http\CurlHttpClient;

final class Epay extends AbstractPayment implements EpayInterface
{
    /**
     * The HTTP client for making requests.
     */
    private HttpClientInterface $httpClient;

    /**
     * The success URL for redirecting after successful payment.
     */
    private string $successUrl;

    /**
     * The failure URL for redirecting after failed payment.
     */
    private string $failureUrl;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $options): void
    {
        if (isset($options['environment'])) {
            if (!in_array($options['environment'], ['test', 'production'], true)) {
                throw new EsewaException('Environment must be either "test" or "production".');
            }
            $this->environment = $options['environment'];
        }

        if (!isset($options['product_code'])) {
            throw new EsewaException('Product code is required.');
        }
        $this->productCode = $options['product_code'];

        if (!isset($options['secret_key'])) {
            throw new EsewaException('Secret key is required.');
        }
        $this->secretKey = $options['secret_key'];

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
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * {@inheritdoc}
     */
    public function createPayment(array $paymentData): array
    {
        $this->validatePaymentData($paymentData);

        $amount = (float) $paymentData['amount'];
        $taxAmount = (float) ($paymentData['tax_amount'] ?? 0);
        $serviceCharge = (float) ($paymentData['product_service_charge'] ?? 0);
        $deliveryCharge = (float) ($paymentData['product_delivery_charge'] ?? 0);
        $totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;

        $transactionUuid = $paymentData['transaction_uuid'];
        $this->validateTransactionUuid($transactionUuid);

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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function verifyPayment(string $encodedResponse): array
    {
        $response = $this->decodeResponse($encodedResponse);

        if (!isset($response['signature'], $response['signed_field_names'])) {
            throw new EsewaException('Invalid response: missing signature or signed fields.');
        }

        // Verify signature
        $signedFields = explode(',', $response['signed_field_names']);
        $dataToVerify = [];
        foreach ($signedFields as $field) {
            if (!isset($response[$field])) {
                throw new EsewaException("Missing signed field: {$field}");
            }
            $dataToVerify[] = "{$field}={$response[$field]}";
        }

        $dataString = implode(',', $dataToVerify);
        $expectedSignature = $this->generateSignature($dataString);

        if (!hash_equals($expectedSignature, $response['signature'])) {
            throw new EsewaException('Invalid signature in response.');
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(string $transactionUuid, float $totalAmount): array
    {
        $url = sprintf(
            '%s/api/epay/transaction/status/?product_code=%s&total_amount=%s&transaction_uuid=%s',
            $this->getBaseUrl('epay'),
            $this->productCode,
            $totalAmount,
            $transactionUuid
        );

        $response = $this->httpClient->get($url);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EsewaException('Invalid JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormActionUrl(): string
    {
        return $this->getBaseUrl('epay') . '/api/epay/main/v2/form';
    }

    /**
     * {@inheritdoc}
     */
    public function verifySignature(array $data, string $signature): bool
    {
        if (!isset($data['signed_field_names'])) {
            return false;
        }

        $signedFields = explode(',', $data['signed_field_names']);
        $dataToVerify = [];
        foreach ($signedFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
            $dataToVerify[] = "{$field}={$data[$field]}";
        }

        $dataString = implode(',', $dataToVerify);
        $expectedSignature = $this->generateSignature($dataString);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Validates payment data.
     *
     * @param array<string, mixed> $paymentData The payment data to validate.
     * @throws EsewaException If validation fails.
     */
    private function validatePaymentData(array $paymentData): void
    {
        if (!isset($paymentData['amount'])) {
            throw new EsewaException('Amount is required.');
        }
        $this->validateAmount((float) $paymentData['amount']);

        if (!isset($paymentData['transaction_uuid'])) {
            throw new EsewaException('Transaction UUID is required.');
        }
    }
}
