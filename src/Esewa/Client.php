<?php declare(strict_types=1);

namespace Cixware\Payment\Esewa;

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
    public function process(string $productId, float $amount, float $taxAmount, float $serviceAmount = 0, float $deliveryAmount = 0): void
    {
        // format form attributes
        $formInputs = [
            'scd' => $this->config->merchantCode,
            'su' => $this->config->successUrl,
            'fu' => $this->config->failureUrl,
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
            $htmlForm .= '<input name="' . $name . '" type="hidden" value="' . $value . '">';
        endforeach;
        $htmlForm .= '</form><script type="text/javascript">document.getElementById("esewa-form").submit();</script>';

        // output the form
        echo $htmlForm;
    }
}
