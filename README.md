# eSewa Payment Gateway SDK for PHP

[![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/esewa-php-sdk?style=flat)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![Build](https://img.shields.io/github/actions/workflow/status/remotemerge/esewa-php-sdk/install.yml?style=flat&logo=github)](https://github.com/remotemerge/esewa-php-sdk/actions)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/esewa-php-sdk.svg?style=flat&label=Downloads)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![License](https://img.shields.io/github/license/remotemerge/esewa-php-sdk?style=flat)](https://github.com/remotemerge/esewa-php-sdk/blob/main/LICENSE)

The **most complete, production-ready PHP SDK for eSewa payment gateway integration in Nepal.** Integrate eSewa ePay v2 into any PHP application - pure PHP, no framework dependency - with HMAC-SHA256 signature verification, sandbox support, and a clean developer API backed by a full test suite.

> Built and maintained by [remotemerge]. For official eSewa API reference, see the [eSewa Developer Portal].

## Why This SDK?

- **Zero framework lock-in** - works with Laravel, Symfony, CodeIgniter, or plain PHP
- **eSewa ePay v2 API** - implements the current HMAC-SHA256 signed payment flow
- **Signature verification built-in** - cryptographically validates every response from eSewa
- **Sandbox & production ready** - switch between environments with a single config key
- **Fully tested** - comprehensive PHPUnit test suite with every release
- **PSR-4 autoloaded** - drop-in Composer install, no manual setup

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | >= 8.1  |
| ext-curl    | any     |
| ext-json    | any     |
| Composer    | >= 2.0  |

## Installation

Install with a single Composer command:

```bash
composer require remotemerge/esewa-php-sdk
```

That's it. Composer handles autoloading - no manual file includes or bootstrapping required.

## Configuration

All SDK features are accessed through a single factory call. `EsewaFactory::createEpay()` validates your configuration and returns a fully wired `EpayInterface` instance ready for use.

### Sandbox (Test) Environment

eSewa provides fixed sandbox credentials for development. Use them freely - no real money moves in `test` mode.

```php
use RemoteMerge\Esewa\EsewaFactory;

$epay = EsewaFactory::createEpay([
    'environment'  => 'test',
    'product_code' => 'EPAYTEST',
    'secret_key'   => '8gBm/:&EnhH.1/q',
    'success_url'  => 'https://example.com/success.php',
    'failure_url'  => 'https://example.com/failed.php',
]);
```

> **Sandbox eSewa account:** username `9806800001` – `9806800005`, password `Nepal@123`, MPIN `1122`, OTP `123456`.
> Full test credentials at [eSewa Developer Portal].

### Production Environment

Retrieve your live `product_code` and `secret_key` from the [eSewa Merchant Dashboard]. Only swap the credentials - everything else stays the same.

```php
use RemoteMerge\Esewa\EsewaFactory;

$epay = EsewaFactory::createEpay([
    'environment'  => 'production',
    'product_code' => 'YOUR_PRODUCT_CODE',
    'secret_key'   => 'YOUR_SECRET_KEY',
    'success_url'  => 'https://example.com/success.php',
    'failure_url'  => 'https://example.com/failed.php',
]);
```

### Configuration Reference

| Option         | Required | Default | Description                                        |
|----------------|----------|---------|----------------------------------------------------|
| `environment`  | No       | `test`  | `test` for sandbox, `production` for live          |
| `product_code` | Yes      | -       | Merchant product code assigned by eSewa            |
| `secret_key`   | Yes      | -       | Secret key used to generate HMAC-SHA256 signatures |
| `success_url`  | Yes      | -       | Redirect URL on successful payment                 |
| `failure_url`  | Yes      | -       | Redirect URL on failed or cancelled payment        |

## Usage

The complete eSewa ePay v2 payment flow has three steps: **initiate → redirect → verify**. The SDK handles cryptographic signing and verification so you can focus on your application logic.

### Step 1 - Initiate Payment

Call `createPayment()` with your order details. The SDK computes the `total_amount` and generates the HMAC-SHA256 signature automatically. Pass `transaction_uuid` as a unique identifier per order - UUID v4 is recommended.

```php
use Ramsey\Uuid\Uuid;
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

$epay = EsewaFactory::createEpay([/* config */]);

try {
    $paymentData = $epay->createPayment([
        'amount'                   => 500.00,
        'tax_amount'               => 65.00,   // 13% VAT
        'product_service_charge'   => 0,
        'product_delivery_charge'  => 100,
        'transaction_uuid'         => Uuid::uuid4()->toString(),
    ]);
} catch (EsewaException $e) {
    echo $e->getMessage();
}
```

`total_amount` is calculated as: `amount + tax_amount + product_service_charge + product_delivery_charge`.

| Field                     | Required | Description                                         |
|---------------------------|----------|-----------------------------------------------------|
| `amount`                  | Yes      | Base product or service price                       |
| `transaction_uuid`        | Yes      | Unique order identifier (alphanumeric + hyphens)    |
| `tax_amount`              | No       | VAT or tax on the order (default: `0`)              |
| `product_service_charge`  | No       | Service fee, if applicable (default: `0`)           |
| `product_delivery_charge` | No       | Delivery/shipping fee, if applicable (default: `0`) |

Render the signed fields as a hidden HTML form and submit to `getFormActionUrl()`. eSewa handles the checkout UI and redirects back to your URLs on completion.

```php
<form action="<?= $epay->getFormActionUrl() ?>" method="POST">
    <?php foreach ($paymentData as $key => $value): ?>
        <input type="hidden"
               name="<?= htmlspecialchars($key) ?>"
               value="<?= htmlspecialchars((string) $value) ?>">
    <?php endforeach; ?>
    <button type="submit">Pay with eSewa</button>
</form>
```

### Step 2 - Verify Payment

After a successful payment, eSewa redirects to your `success_url` with a `data` query parameter - a base64-encoded JSON payload signed by eSewa. **Always verify this signature server-side** before marking an order as paid.

```php
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

$epay = EsewaFactory::createEpay([/* config */]);

$encodedResponse = $_GET['data'] ?? null;
if ($encodedResponse === null) {
    // No eSewa response - reject the request
    exit;
}

try {
    $payment = $epay->verifyPayment($encodedResponse);

    // Signature verified - safe to mark order as paid
    $payment['transaction_code'];   // eSewa transaction reference
    $payment['status'];             // "COMPLETE" on success
    $payment['total_amount'];       // Total charged amount
    $payment['transaction_uuid'];   // Your original order UUID
} catch (EsewaException $e) {
    // Invalid signature or malformed response - do NOT fulfil the order
    echo $e->getMessage();
}
```

`verifyPayment()` decodes the response, reconstructs the signed string from `signed_field_names`, computes the expected HMAC-SHA256 signature, and compares it using timing-safe `hash_equals()`. An `EsewaException` is thrown on any mismatch.

### Step 3 - Check Transaction Status (Optional)

For server-to-server confirmation independent of the redirect flow - useful for webhooks, background jobs, or fraud checks - query the eSewa status API directly.

```php
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

$epay = EsewaFactory::createEpay([/* config */]);

try {
    $status = $epay->checkStatus(
        transactionUuid: 'your-order-uuid',
        totalAmount: 665.00,
    );

    echo $status['status']; // "COMPLETE", "PENDING", "FAILED", etc.
} catch (EsewaException $e) {
    echo $e->getMessage();
}
```

### Manual Signature Verification

If you decode the eSewa response payload yourself (e.g., from a stored record), use `verifySignature()` to validate it independently of `verifyPayment()`.

```php
$isValid = $epay->verifySignature($responseData, $responseData['signature']);

if ($isValid) {
    // Payload is authentic
}
```

## Error Handling

All SDK methods throw `RemoteMerge\Esewa\Exceptions\EsewaException` on failure. Wrap every call in a `try/catch` block and never fulfil an order without a successful `verifyPayment()`.

```php
use RemoteMerge\Esewa\Exceptions\EsewaException;

try {
    $payment = $epay->verifyPayment($encodedResponse);
} catch (EsewaException $e) {
    // Log the error, show a user-friendly message, do NOT mark the order paid
    error_log($e->getMessage());
}
```

Common exceptions:

| Message                                    | Cause                                     |
|--------------------------------------------|-------------------------------------------|
| `Amount must be greater than 0`            | Invalid payment amount passed             |
| `Transaction UUID must be alphanumeric...` | UUID contains unsupported characters      |
| `Invalid signature in response`            | Response was tampered or wrong secret key |
| `Failed to decode response data`           | `data` parameter is not valid base64      |
| `Product code is required`                 | Missing `product_code` in configuration   |

## Getting Help

- **eSewa account or merchant issues** - contact [eSewa] directly.
- **SDK bugs or feature requests** - [open an issue](https://github.com/remotemerge/esewa-php-sdk/issues/new) on GitHub.
- **API reference** - [eSewa Developer Portal].

## Contributing

Contributions are welcome. To maintain quality across the codebase, please follow these guidelines:

- Code must conform to [PER Coding Style 3.0] standards - run `vendor/bin/php-cs-fixer fix` before submitting.
- All changes must pass the full test suite - run `vendor/bin/phpunit`.
- Keep pull requests focused; one change per PR.
- Submit against the `main` branch.

## License

MIT © [remotemerge]

[eSewa]: https://esewa.com.np

[remotemerge]: https://github.com/remotemerge

[eSewa Developer Portal]: https://developer.esewa.com.np

[eSewa Merchant Dashboard]: https://merchant.esewa.com.np

[PER Coding Style 3.0]: https://www.php-fig.org/per/coding-style/