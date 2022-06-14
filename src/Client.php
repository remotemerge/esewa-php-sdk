<?php declare(strict_types=1);

namespace Cixware\Esewa;

use Cixware\Esewa\Payment\Payment;

final class Client extends Base
{
    use Payment;

    public function __construct(array $configs)
    {
        // init configs
        $this->init($configs);
    }
}
