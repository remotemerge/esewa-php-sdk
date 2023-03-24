<?php declare(strict_types=1);

namespace Cixware\Esewa;

use GuzzleHttp\Exception\GuzzleException;

final class Client
{
    private ?Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * This method creates the form in runtime and post the data to eSewa server.
     */
    public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0.0, float $deliveryAmount = 0.0): void
    {
        // format form attributes
        $formInputs = [
            'scd' => $this->config->merchantCode,
            'su' => $this->config->successUrl,
            'fu' => $this->config->failureUrl . '?' . http_build_query(['pid' => $productId]),
            'pid' => $productId,
            'amt' => $amount,
            'txAmt' => $taxAmount,
            'psc' => $serviceAmount,
            'pdc' => $deliveryAmount,
            'tAmt' => $amount + $taxAmount + $serviceAmount + $deliveryAmount,
        ];

        // generate form from attributes
        $htmlForm = '<form method="POST" action="' . ($this->config->apiUrl . '/epay/main') . '" id="esewa-form">';

        foreach ($formInputs as $name => $value):
            $htmlForm .= sprintf('<input name="%s" type="hidden" value="%s">', $name, $value);
        endforeach;

        $htmlForm .= '</form><script type="text/javascript">document.getElementById("esewa-form").submit();</script>';

        // output the form
        echo $htmlForm;
    }

    /**
     * This method verifies the payment using the reference ID.
     * @throws GuzzleException
     */
    public function verify(string $referenceId, string $productId, float $amount): bool
    {
        // init Guzzle client
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->config->apiUrl,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'Accept' => 'application/xml',
            ],
            'allow_redirects' => [
                'protocols' => ['https'],
            ],
        ]);

        // init verification request
        $request = $client->post('/epay/transrec', [
            'form_params' => [
                'scd' => $this->config->merchantCode,
                'rid' => $referenceId,
                'pid' => $productId,
                'amt' => $amount,
            ],
        ]);

        // grab response and parse the XML
        $response = $this->parseXml($request->getBody()->getContents());

        // check for "success" or "failure" status
        return strtolower($response) === 'success';
    }

    /**
     * This method parse XML string and return the object.
     */
    private function parseXml(string $xmlStr): string
    {
        // Load the XML string
        $xml = simplexml_load_string($xmlStr);
        // extract the value
        return trim((string)$xml->response_code);
    }
}
