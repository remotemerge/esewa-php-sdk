# eSewa SDK for PHP

[![@cixware on Twitter](https://img.shields.io/badge/Twitter-%40cixware-blue.svg?style=flat&logo=twitter)](https://twitter.com/cixware)
[![@cixware on Facebook](https://img.shields.io/badge/Facebok-%40cixware-blue.svg?style=flat&logo=facebook)](https://www.facebook.com/cixware)
![PHP Version](https://img.shields.io/packagist/php-v/cixware/esewa-php-sdk)
![Build](https://img.shields.io/github/workflow/status/cixware/esewa-php-sdk/Install?logo=github)
[![Downloads](https://img.shields.io/packagist/dt/cixware/esewa-php-sdk.svg?style=flat&label=Downloads)](https://packagist.org/packages/cixware/esewa-php-sdk)
![License](https://img.shields.io/github/license/cixware/esewa-php-sdk)

The **eSewa SDK for PHP** makes it easy for developers to access [eSewa] payment service in their PHP code. This repo
and the package are developed and maintained by [Cixware]. You can find more details in [eSewa Documentation] site.

# Getting Started

1. **Sign up for eSewa** – Before you begin, you need to sign up and retrieve your credentials from [eSewa].
2. **Minimum requirements** – To run the SDK, your system will need to meet the minimum requirements, including
   having **PHP >= 7.4**. We highly recommend having it compiled with the cURL extension and cURL compiled with a TLS
   backend (e.g., NSS or OpenSSL). We do not support outdated PHP versions.

# Installation

**Install the SDK** – Using [Composer] is the recommended way to install the eSewa SDK for PHP. The SDK is available
via [Packagist] under the [`cixware/esewa-php-sdk`][install-packagist] package.

```
composer require cixware/esewa-php-sdk
```

## Getting Help

We use the GitHub issues for tracking bugs and feature requests and address them as quickly as possible.

* Call or Email [eSewa] for account and payment related queries.
* If it turns out that you may have found a bug,
  please [open an issue](https://github.com/cixware/esewa-php-sdk/issues/new).
* For further development and integration in your application contact [Cixware].

## Quick Examples

### Create a eSewa client

```php
// init composer autoloader.
require 'vendor/autoload.php';

use Cixware\Esewa\Client;
use Cixware\Esewa\Config;

// set success and failure callback urls
$successUrl = 'https://example.com/success.php';
$failureUrl = 'https://example.com/failed.php';

// config for development
$config = new Config($successUrl, $failureUrl);

// config for production
$config = new Config($successUrl, $failureUrl, 'b4e...e8c753...2c6e8b', 'production');

// initialize eSewa client
$esewa = new Client($config);
```

Here `b4e...e8c753...2c6e8b` is merchant code retried from eSewa.

### Make Payment

This will redirect the user to eSewa dashboard for payment processing. Once the payment is successful, it will redirect
to your success URL. If the payment fails, it will redirect to your failure URL.

```php
$esewa->process('P101W201', 100, 15, 80, 50);
```

The method accepts 5 parameters.

1. `pid` A unique ID of product or item or ticket etc.
2. `amt` Amount of product or item or ticket etc
3. `txAmt` Tax amount on product or item or ticket etc. Pass `0` if Tax/VAT is not applicable.
4. `psc` The service charge (if applicable); default to `0`.
5. `pdc` The delivery charge (if applicable); default to `0`.

The `tAmt` total amount is auto calculated based on the parameters.

### Verify Payment

Transaction verification process provide you filter that identify potentially fraudulent transactions and screen against
data such as the value of transaction amount etc.

```php
$status = $esewa->verify('R101', 'P101W201', 245);
if ($status) {
    // verification successful
}
```

The method accepts 3 parameters.

1. `refId` A unique payment reference code generated by eSewa.
2. `oid` Product ID used on payment request.
3. `tAmt` Total payment amount (including Tax/VAT and other charges.)

**Note:** You can extract `refId` from the success response url parameter.

# Contribution

We in [Cixware] :heart: Open Source Software and welcome the community for contribution. Please follow the guidelines.

* Your code must follow [PSR] standards.
* Your code must pass the tests.
* You must document and write clean code.
* Make PR in `master` branch.

Thanks for your contribution and support :relaxed:

[eSewa]: https://esewa.com.np

[eSewa Documentation]: https://developer.esewa.com.np

[eSewa Contact]: https://blog.esewa.com.np/contact-us/

[Cixware]: https://cixware.io

[composer]: http://getcomposer.org

[packagist]: http://packagist.org

[install-packagist]: https://packagist.org/packages/cixware/esewa-php-sdk

[PSR]: https://www.php-fig.org/psr