<?php

namespace Cixware\Esewa\Payment;

use Cixware\Esewa\Exception\EsewaException;
use Cixware\Esewa\Helpers\Configure;
use GuzzleHttp\Client;
use stdClass;

class Payment implements CreateInterface, VerifyInterface
{
    use Configure;

    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var string $userAgent
     */
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36';

    public function __construct()
    {
        $this->client = new Client([
            'defaults' => [
                'headers' => [
                    'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? $this->userAgent,
                ],
            ],
            'base_uri' => getenv('ESEWA_BASE_URL'),
        ]);
    }

    /**
     * ---------------------------------------------------
     * This will create the payment form in runtime and post
     * the data to eSewa payment server.
     * ---------------------------------------------------
     * @param string $productId
     * @param float $amount
     * @param float $taxAmount
     * @param float $serviceAmount
     * @param float $deliveryAmount
     * @throws EsewaException
     * ---------------------------------------------------
     */
    public function create(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void
    {
        // check success url
        if (filter_var(getenv('ESEWA_SUCCESS_URL'), FILTER_VALIDATE_URL)) {
            throw new EsewaException('The success_url is required and must be a valid URL.');
        }

        // check failure url
        if (filter_var(getenv('ESEWA_FAILURE_URL'), FILTER_VALIDATE_URL)) {
            throw new EsewaException('The failure_url is required and must be a valid URL.');
        }

        // create form params
        $attach = new stdClass();
        $attach->url = getenv('ESEWA_BASE_URL') . 'main';
        $attach->productId = $productId;
        $attach->amount = $amount;
        $attach->taxAmount = $taxAmount;
        $attach->serviceAmount = $serviceAmount;
        $attach->deliveryAmount = $deliveryAmount;
        $attach->totalAmount = $amount + $taxAmount + $serviceAmount + $deliveryAmount;
        // load HTML file
        require dirname(__DIR__) . '/Helpers/form.php';
    }

    /**
     * ---------------------------------------------------
     * This will verify the payment using the reference ID.
     * ---------------------------------------------------
     * @param string $referenceId
     * @param string $productId
     * @param float $amount
     * @return object
     * ---------------------------------------------------
     */
    public function verify(string $referenceId, string $productId, float $amount): object
    {
        $status = new stdClass();
        $status->{'verified'} = false;

        // init params
        $params = [
            'scd' => getenv('ESEWA_MERCHANT_CODE'),
            'rid' => $referenceId,
            'pid' => $productId,
            'amt' => $amount,
        ];
        // init request
        $request = $this->client->post('transrec', [
            'headers' => ['Accept' => 'application/xml'],
            'form_params' => $params,
        ]);
        // grab response
        $response = $this->parseXML($request->getBody()->getContents());
        // parse XML and check
        if (isset($response->response_code) && strtolower(trim($response->response_code)) === 'success') {
            $status->verified = true;
        }
        return $status;
    }

    /**
     * ---------------------------------------------------
     * This will parse XML string and return the object.
     * ---------------------------------------------------
     * @param string $str
     * @return mixed
     * ---------------------------------------------------
     */
    private function parseXML(string $str)
    {
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode((array)$xml), false);
    }
}
