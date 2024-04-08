# eSewa SDK for PHP

[![X (formerly Twitter)](https://img.shields.io/badge/-@sapkotamadan-white?style=flat&logo=x&label=(formerly%20Twitter))](https://twitter.com/sapkotamadan)
[![Facebook](https://img.shields.io/badge/Facebook-NextSapkotaMadan-blue?style=flat&logo=facebook)](https://www.facebook.com/NextSapkotaMadan)
![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/esewa-php-sdk)
![Build](https://img.shields.io/github/actions/workflow/status/remotemerge/esewa-php-sdk/install.yml?branch=main&style=flat&logo=github)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/esewa-php-sdk.svg?style=flat&label=Downloads)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
![License](https://img.shields.io/github/license/remotemerge/esewa-php-sdk)

The **eSewa SDK for PHP** is developed and maintained by [remotemerge] and the community, simplifying the integration of the eSewa payment service into PHP code. For more details, refer to the [eSewa Documentation] website.

## Getting Started

1. **Sign up for eSewa** – Before you begin, you need to sign up and retrieve your credentials from [eSewa].
2. **Minimum requirements** – To run the SDK, your system will need to meet the minimum requirements, including having **PHP >= 7.4**. We highly recommend having it compiled with the cURL extension and cURL compiled with a TLS backend (e.g., NSS or OpenSSL).

## Installation

**Install the SDK** – Using Composer is the recommended way to install the eSewa SDK for PHP. The SDK is available via Packagist under the [`remotemerge/esewa-php-sdk`][install-package] package.

```
composer require remotemerge/esewa-php-sdk
```

## Getting Help

Bugs and feature requests are tracked using GitHub issues, and prioritization is given to addressing them as soon as possible.

* For account and payment related concerns, please contact [eSewa] directly by calling or emailing them.
* If a bug is identified, please [open an issue](https://github.com/remotemerge/esewa-php-sdk/issues/new) on GitHub.
* For assistance with integrating eSewa into your application, feel free to reach out to the support team.

## Quick Examples

### Create an eSewa client

```php
// Init composer autoloader.
require 'vendor/autoload.php';

use RemoteMerge\Esewa\Client;
use RemoteMerge\Esewa\Config;

// Set success and failure callback URLs.
$successUrl = 'https://example.com/success.php';
$failureUrl = 'https://example.com/failed.php';

// Config for development.
$config = new Config($successUrl, $failureUrl);

// Config for production.
$config = new Config($successUrl, $failureUrl, 'b4e...e8c753...2c6e8b');

// Initialize eSewa client.
$esewa = new Client($config);
```

Here `b4e...e8c753...2c6e8b` is merchant code retrieved from eSewa.

### Make Payment

When the user initiates the payment process, the package redirects the user to an eSewa site for payment processing. The
eSewa system will redirect the user to your specified success URL if the payment is successful and to the failure URL if
the payment fails.

```php
$esewa->process('P101W201', 100, 15, 80, 50);
```

The method accepts five parameters.

```text
process(string $pid, float $amt, float $txAmt = 0, float $psc = 0, float $pdc = 0)
```

1. `pid` A unique ID of product or item or ticket etc.
2. `amt` Amount of product or item or ticket etc
3. `txAmt` Tax amount on product or item or ticket etc. Pass `0` if Tax/VAT is not applicable.
4. `psc` The service charge (if applicable); default to `0`.
5. `pdc` The delivery charge (if applicable); default to `0`.

### OTP for Payment

When using the eSewa payment gateway in production mode, an OTP (One-Time Password) code is sent to the customer's mobile number to verify the transaction. In development mode, the OTP code is a fixed six-digit number, `123456`, for testing purposes.

### Verify Payment

The verification process identifies potentially fraudulent transactions and checks them against data such as transaction
amount and other parameters.

```php
$status = $esewa->verify('R101', 'P101W201', 245);
if ($status) {
    // Verification successful.
}
```

The method accepts three parameters.

```text
verify(string $refId, string $oid, float $tAmt)
```

1. `refId` A unique payment reference code generated by eSewa.
2. `oid` Product ID used on payment request.
3. `tAmt` Total payment amount (including Tax/VAT and other charges.)

**Note:** You can extract `refId` from the success response url parameter.

## Contribution

The contributions of the Open Source community are highly valued and appreciated. To ensure a smooth and efficient process, please adhere to the following guidelines when submitting code:

- Ensure that the code adheres to [PSR] standards.
- All submitted code must pass relevant tests.
- Proper documentation and clean code practices are essential.
- Please make pull requests to the `main` branch.

Thank you for your support and contributions. Looking forward to reviewing your code.

[eSewa]: https://esewa.com.np

[remotemerge]: https://github.com/remotemerge

[eSewa Documentation]: https://developer.esewa.com.np

[install-package]: https://packagist.org/packages/remotemerge/esewa-php-sdk

[PSR]: https://www.php-fig.org/psr