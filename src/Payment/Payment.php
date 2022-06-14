<?php declare(strict_types=1);

namespace Cixware\Esewa\Payment;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use SimpleXMLElement;
use stdClass;

trait Payment
{
    /**
     * ---------------------------------------------------
     * This will verify the payment using the reference ID.
     * @param string $referenceId
     * @param string $productId
     * @param float $amount
     * @throws GuzzleException
     * @throws JsonException
     * ---------------------------------------------------
     */
    public function verify(string $referenceId, string $productId, float $amount): stdClass
    {
        $status = new stdClass();
        $status->{'verified'} = false;

        // init params
        $params = [
            'scd' => self::$merchantCode,
            'rid' => $referenceId,
            'pid' => $productId,
            'amt' => $amount,
        ];

        // init request
        $request = self::$client->post('/epay/transrec', [
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
     * This will create the payment form in runtime and post
     * the data to eSewa payment server.
     * ---------------------------------------------------
     *
     * @param string $productId
     * @param float $amount
     * @param float $taxAmount
     * @param float $serviceAmount
     * @param float $deliveryAmount
     * ---------------------------------------------------
     */
    public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void
    {
        // create form params
        $attach = new stdClass;

        $attach->url = self::$baseUrl . '/epay/main';
        $attach->successUrl = self::$successUrl;
        $attach->failureUrl = self::$failureUrl;

        $attach->merchantCode = self::$merchantCode;

        $attach->productId = $productId;
        $attach->amount = $amount;
        $attach->taxAmount = $taxAmount;
        $attach->serviceAmount = $serviceAmount;
        $attach->deliveryAmount = $deliveryAmount;
        $attach->totalAmount = $amount + $taxAmount + $serviceAmount + $deliveryAmount;
        // load HTML content
        require dirname(__DIR__) . '/Helpers/form.php';
    }

    /**
     * ---------------------------------------------------
     * This will parse XML string and return the object.
     * ---------------------------------------------------
     * @param string $str
     * @return mixed
     * ---------------------------------------------------
     * @throws JsonException
     */
    private function parseXML(string $str)
    {
        $xml = simplexml_load_string($str, SimpleXMLElement::class, LIBXML_NOCDATA);
        return json_decode(json_encode((array)$xml, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }
}
