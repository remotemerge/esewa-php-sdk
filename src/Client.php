<?php

declare(strict_types=1);

namespace RemoteMerge\Esewa;

use Exception;
use RemoteMerge\Esewa\Exceptions\EsewaException;

class Client extends Config implements ClientInterface
{
    public function __construct(private readonly array $configs = [])
    {
        parent::__construct($this->configs);
    }

    /**
     * This method creates the form in runtime and post the data to eSewa server.
     */
    public function payment(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0.0, float $deliveryAmount = 0.0): void
    {
        // format form attributes
        $formInputs = [
            'scd' => $this->getMerchantCode(),
            'su' => $this->getSuccessUrl(),
            'fu' => $this->getFailureUrl() . '?' . http_build_query(['pid' => $productId]),
            'pid' => $productId,
            'amt' => $amount,
            'txAmt' => $taxAmount,
            'psc' => $serviceAmount,
            'pdc' => $deliveryAmount,
            'tAmt' => $amount + $taxAmount + $serviceAmount + $deliveryAmount,
        ];

        // generate form from attributes
        $htmlForm = '<form method="POST" action="' . ($this->getApiUrl() . '/epay/main') . '" id="esewa-form">';

        foreach ($formInputs as $name => $value):
            $htmlForm .= sprintf('<input name="%s" type="hidden" value="%s">', $name, $value);
        endforeach;

        $htmlForm .= '</form><script type="text/javascript">document.getElementById("esewa-form").submit();</script>';

        // output the form
        echo $htmlForm;
    }

    /**
     * This method verifies the payment using the reference ID.
     * @throws Exception
     */
    public function verifyPayment(string $referenceId, string $productId, float $amount): bool
    {
        // Initialize a cURL handle
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrl() . '/epay/transrec');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/xml',
        ]);

        // Set the request data
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'scd' => $this->getMerchantCode(),
            'rid' => $referenceId,
            'pid' => $productId,
            'amt' => $amount,
        ]));

        // Send the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch) !== 0) {
            // Handle errors here
            throw new EsewaException(curl_error($ch));
        }

        // Close the cURL handle
        curl_close($ch);

        // Parse the XML response
        $status = $this->parseXml($response);

        // check for "success" or "failure" status
        return strtolower($status) === 'success';
    }

    /**
     * This method parse XML string and return the object.
     */
    private function parseXml(string $xmlStr): string
    {
        // Load the XML string
        $xml = simplexml_load_string($xmlStr);
        // extract the value
        return trim((string) $xml->response_code);
    }
}
