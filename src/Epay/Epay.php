<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa\Epay;

use RemoteMerge\Esewa\AbstractPayment;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClient;
use RemoteMerge\Esewa\Http\HttpClientInterface;

final class Epay extends AbstractPayment implements EpayInterface
{
    /**
     * The success URL for redirecting after a successful payment.
     */
    private string $successUrl;

    /**
     * The failure URL for redirecting after a failed payment.
     */
    private string $failureUrl;

    public function __construct(private readonly ?HttpClientInterface $httpClient = new HttpClient())
    {
        //
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
        $this->validateAmount((float) $paymentData['amount']);
        $this->validateRequiredField($paymentData, 'transaction_uuid', 'Transaction UUID');

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
     * {@inheritDoc}
     * @throws EsewaException
     */
    public function verifyPayment(string $encodedResponse): array
    {
        $response = $this->decodeResponse($encodedResponse);

        if (!isset($response['signature'], $response['signed_field_names'])) {
            throw new EsewaException('Invalid response: missing signature or signed fields.');
        }

        // Verify signature
        $signedFields = explode(',', (string) $response['signed_field_names']);
        $dataToVerify = [];
        foreach ($signedFields as $signedField) {
            if (!isset($response[$signedField])) {
                throw new EsewaException('Missing signed field: ' . $signedField);
            }

            $dataToVerify[] = sprintf('%s=%s', $signedField, $response[$signedField]);
        }

        $dataString = implode(',', $dataToVerify);
        $expectedSignature = $this->generateSignature($dataString);

        if (!hash_equals($expectedSignature, $response['signature'])) {
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

        $signedFields = explode(',', (string) $data['signed_field_names']);
        $dataToVerify = [];
        foreach ($signedFields as $signedField) {
            if (!isset($data[$signedField])) {
                return false;
            }

            $dataToVerify[] = sprintf('%s=%s', $signedField, $data[$signedField]);
        }

        $dataString = implode(',', $dataToVerify);
        $expectedSignature = $this->generateSignature($dataString);

        return hash_equals($expectedSignature, $signature);
    }
}
