<?php

namespace Cixware\Esewa;

use Cixware\Esewa\Payment\Payment;

final class Client extends Base
{
    use Payment;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        // init configs
        $this->init($configs);
    }
}
