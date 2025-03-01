<?php

declare(strict_types=1);

// init autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

exit(print_r($_REQUEST, true));
