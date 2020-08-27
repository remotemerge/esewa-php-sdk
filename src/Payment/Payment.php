<?php

namespace Cixware\Esewa\Payment;

use stdClass;

trait Payment
{
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
        $attach = new stdClass();

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
     * This will verify the payment using the reference ID.
     * ---------------------------------------------------
     *
     * @param string $referenceId
     * @param string $productId
     * @param float $amount
     *
     * @return object
     * ---------------------------------------------------
     */
    public function verify(string $referenceId, string $productId, float $amount): object
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
     * This will parse XML string and return the object.
     * ---------------------------------------------------
     *
     * @param string $str
     *
     * @return mixed
     * ---------------------------------------------------
     */
    private function parseXML(string $str)
    {
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode((array)$xml), false);
    }
}
