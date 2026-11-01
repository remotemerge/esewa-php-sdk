# **eSewa PHP SDK: Payment Gateway Integration for PHP**

[![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/esewa-php-sdk?logo=php&style=flat)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![Tests](https://img.shields.io/github/actions/workflow/status/remotemerge/esewa-php-sdk/test.yml?style=flat&logo=counterstrike&label=test)](https://github.com/remotemerge/esewa-php-sdk/actions)
[![Build](https://img.shields.io/github/actions/workflow/status/remotemerge/esewa-php-sdk/install.yml?style=flat&logo=github)](https://github.com/remotemerge/esewa-php-sdk/actions)
[![Sonar Quality](https://img.shields.io/sonar/quality_gate/esewa-php-sdk/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubecloud&logoColor=126ED3&label=quality)](https://sonarcloud.io/summary/overall?id=esewa-php-sdk&branch=main)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/esewa-php-sdk/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubeserver&logoColor=126ED3)](https://sonarcloud.io/summary/overall?id=esewa-php-sdk&branch=main)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/esewa-php-sdk.svg?style=flat&label=downloads)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![License](https://img.shields.io/github/license/remotemerge/esewa-php-sdk)](https://github.com/remotemerge/esewa-php-sdk?tab=MIT-1-ov-file)

A production-ready PHP SDK for integrating [eSewa] payment gateway into any PHP application. The SDK implements the eSewa ePay v2 API with HMAC-SHA256 signature verification, full sandbox support, and a clean developer API. It works with any PHP framework or without one and handles the entire payment lifecycle from initiation through cryptographic verification.

> For official eSewa API reference, see the [eSewa Developer Portal].

## **Table of Contents**

| #  | Title                                             | Description                                                             |
|----|---------------------------------------------------|-------------------------------------------------------------------------|
| 1  | [Why eSewa PHP SDK?](#why-esewa-php-sdk)          | What makes this SDK a good choice for eSewa payment integration.        |
| 2  | [Key Features](#key-features)                     | Signing, verification, status checks, validation, and extensibility.    |
| 3  | [Compatibility](#compatibility)                   | PHP version, required extensions, and supported frameworks.             |
| 4  | [Get Started in Minutes](#get-started-in-minutes) | Install via Composer and verify the setup with a quick test.            |
| 5  | [Configuration](#configuration)                   | Sandbox and production environment setup with all available options.    |
| 6  | [Basic Usage](#basic-usage)                       | Initiate payments, verify responses, and check transaction status.      |
| 7  | [Advanced Usage](#advanced-usage)                 | Manual signature verification, custom HTTP clients, and helper methods. |
| 8  | [Error Handling](#error-handling)                 | Exception categories, messages, and recommended handling patterns.      |
| 9  | [Security](#security)                             | How HMAC-SHA256 signing works and best practices for production.        |
| 10 | [Try with Docker](#try-with-docker)               | Run the included demo store locally in a Docker container.              |
| 11 | [Getting Help](#getting-help)                     | Where to report bugs, request features, and find support.               |
| 12 | [Contributing](#contributing)                     | Coding standards, testing, and pull request guidelines.                 |

## **Why eSewa PHP SDK?**

eSewa PHP SDK is a focused, well-tested library that handles the full eSewa ePay v2 payment flow in PHP. It takes care of HMAC-SHA256 signature generation, response verification with timing-safe comparison, and transaction status checks so that developers can focus on their application logic instead of cryptographic plumbing. The SDK runs on PHP 8.1+, installs through Composer with PSR-4 autoloading, and works equally well in Laravel, Symfony, CodeIgniter, or plain PHP without any framework dependency.

---

## **Key Features**

✅ **ePay v2 Payment Initiation**
Creates signed payment forms with auto-calculated totals, ready to submit to the eSewa checkout page.

✅ **HMAC-SHA256 Signature Generation**
Automatically signs all outgoing payment requests using your secret key, following the eSewa ePay v2 specification.

✅ **Cryptographic Response Verification**
Decodes base64-encoded eSewa responses, requires the payment result fields to be signed, and verifies the signature using timing-safe `hash_equals()`.

✅ **Transaction Status API**
Provides a server-to-server method for querying payment status directly from the eSewa API, independent of the redirect flow.

✅ **Sandbox Environment**
Full sandbox support with eSewa-provided test credentials for development and testing without real transactions.

✅ **Input Validation**
Validates all payment parameters, configuration options, and API responses with clear, actionable error messages.

✅ **Custom HTTP Client**
Accepts an injectable HTTP client via `HttpClientInterface` for proxy configuration, custom timeouts, or request logging.

---

## **Compatibility**

| Requirement | Version | Notes                                       |
|-------------|---------|---------------------------------------------|
| PHP         | >= 8.1  | Requires `ext-curl` and `ext-json`          |
| Composer    | >= 2.0  | For package installation and autoloading    |
| Frameworks  | Any     | Laravel, Symfony, CodeIgniter, or plain PHP |

---

## **Get Started in Minutes**

Adding eSewa PHP SDK to a project is quick. The library requires **PHP 8.1** or higher.

### **Installation**

Install the library via Composer:

```bash
composer require remotemerge/esewa-php-sdk
```

Composer handles autoloading automatically. No manual file includes or bootstrapping required.

---

## **Configuration**

All SDK features are accessed through `EsewaFactory::createEpay()`. This factory method validates the provided configuration and returns a fully wired `EpayInterface` instance ready for use.

### **Sandbox (Test) Environment**

eSewa provides fixed sandbox credentials for development and testing. No real money moves in `test` mode, so these credentials can be used freely during development.

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

> **Sandbox credentials:** eSewa ID `9806800001` – `9806800005`, password `Nepal@123`, MPIN `1122`, OTP `123456`.
> Full test credentials at [eSewa Developer Portal].

### **Production Environment**

For production, retrieve the live `product_code` and `secret_key` from the [eSewa Merchant Dashboard] and swap them in. Everything else stays the same.

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

### **Configuration Reference**

| Option         | Required | Default | Type     | Description                                        |
|----------------|----------|---------|----------|----------------------------------------------------|
| `environment`  | No       | `test`  | `string` | `test` for sandbox, `production` for live          |
| `product_code` | Yes      | -       | `string` | Merchant product code assigned by eSewa            |
| `secret_key`   | Yes      | -       | `string` | Secret key used to generate HMAC-SHA256 signatures |
| `success_url`  | Yes      | -       | `string` | Redirect URL on successful payment                 |
| `failure_url`  | Yes      | -       | `string` | Redirect URL on failed or cancelled payment        |

## **Basic Usage**

The eSewa ePay v2 payment flow has four steps: **initiate → redirect → verify → confirm**. The SDK handles cryptographic signing, callback verification and the status request, so the integration code stays simple.

### **Initiate a Payment**

Call `createPayment()` with order details. The SDK computes the `total_amount` and generates the HMAC-SHA256 signature automatically. The `transaction_uuid` should be a unique identifier per order. UUID is recommended.

```php
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

$epay = EsewaFactory::createEpay([
    'environment'  => 'test',
    'product_code' => 'EPAYTEST',
    'secret_key'   => '8gBm/:&EnhH.1/q',
    'success_url'  => 'https://example.com/success.php',
    'failure_url'  => 'https://example.com/failed.php',
]);

try {
    $paymentData = $epay->createPayment([
        'amount'                   => 500.00,
        'tax_amount'               => 65.00,
        'product_service_charge'   => 0,
        'product_delivery_charge'  => 100,
        'transaction_uuid'         => bin2hex(random_bytes(16)),
    ]);
} catch (EsewaException $e) {
    echo $e->getMessage();
}
```

**Payment Parameters:**

| Field                     | Required | Type     | Description                                         |
|---------------------------|----------|----------|-----------------------------------------------------|
| `amount`                  | Yes      | `float`  | Base product or service price                       |
| `transaction_uuid`        | Yes      | `string` | Unique order identifier (alphanumeric + hyphens)    |
| `tax_amount`              | No       | `float`  | VAT or tax on the order (default: `0`)              |
| `product_service_charge`  | No       | `float`  | Service fee, if applicable (default: `0`)           |
| `product_delivery_charge` | No       | `float`  | Delivery/shipping fee, if applicable (default: `0`) |
| `properties`              | No       | `array`  | Up to three custom fields (see below)               |

The `total_amount` is calculated automatically as `amount + tax_amount + product_service_charge + product_delivery_charge`.

### **Custom Properties**

eSewa accepts up to three optional custom fields per payment. Pass them as a `properties` map, and `createPayment()` returns each one as a `properties[name]` form field:

```php
$paymentData = $epay->createPayment([
    'amount'           => 100,
    'transaction_uuid' => 'order-123',
    'properties'       => [
        'customer_name'  => 'Example Customer',
        'customer_id'    => 11,
        'invoice_number' => 'INV-123',
    ],
]);

// Added to the returned form data:
// 'properties[customer_name]'  => 'Example Customer'
// 'properties[customer_id]'    => '11'
// 'properties[invoice_number]' => 'INV-123'
```

- At most three entries. Names must be non-empty strings without `[` or `]`.
- Values may be `string`, `int` or `float` and are cast to string. `bool`, `null`, arrays and objects throw `EsewaException`.
- Properties are not signed, so the signature and `signed_field_names` are the same with or without them.
- eSewa does not document whether properties are returned in the callback, so do not rely on reading them back.

### **Render the Payment Form**

Render the signed fields as a hidden HTML form and submit it to `getFormActionUrl()`. eSewa handles the checkout UI on their end and redirects users back to the configured URLs when the payment completes or fails.

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

### **Verify and Confirm a Payment**

After a payment, eSewa redirects to the `success_url` with a `data` query parameter containing a base64-encoded JSON payload signed by eSewa. A valid signature proves the payload came from eSewa, but it is **not** enough to mark the order paid. eSewa requires merchants to confirm the transaction with a status request, and to stop delivery when the confirmed amount differs from the requested amount.

Mark an order paid only after all of these steps succeed:

1. **Verify** the callback with `verifyPayment()`.
2. **Match** `transaction_uuid` and `total_amount` against your own order record. eSewa may format the amount (for example `"1,000.0"`), so strip the formatting only after verification and compare exact values, such as integer paisa, rather than floats.
3. **Confirm** with `checkStatus()`, using the UUID and total from your order record.
4. **Accept** only the `COMPLETE` status. Treat `PENDING`, `AMBIGUOUS`, any other status, a mismatch or an exception as unconfirmed, and keep the UUID so you can check it again later.
5. **Fulfil once.** Make the paid transition atomic and idempotent, so refreshes, repeated callbacks or repeated status checks cannot fulfil the same order twice. For example, run `UPDATE orders SET status = 'paid' WHERE uuid = ? AND status = 'pending'` and fulfil only when it changes one row.

```php
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

function toPaisa(string|int|float $amount): int
{
    return (int) round((float) str_replace(',', '', (string) $amount) * 100);
}

$epay = EsewaFactory::createEpay([/* config */]);

$encodedResponse = $_GET['data'] ?? null;
if ($encodedResponse === null) {
    exit('No eSewa response');
}

try {
    // 1. Verify: the payload is signed by eSewa for this merchant
    $payment = $epay->verifyPayment($encodedResponse);

    // 2. Match: compare with your own order record
    $order = findOrderByUuid($payment['transaction_uuid']); // Your own lookup
    if ($order === null || toPaisa($payment['total_amount']) !== toPaisa($order['total_amount'])) {
        exit('Payment does not match the order'); // Do NOT fulfil
    }

    // 3. Confirm: ask eSewa, using your own UUID and total
    $status = $epay->checkStatus($order['uuid'], $order['total_amount']);

    // 4. Accept only COMPLETE, and 5. fulfil once
    if (($status['status'] ?? null) === 'COMPLETE') {
        markOrderPaidOnce($order['uuid']); // Your own atomic, idempotent update
    } else {
        // Not confirmed yet: keep the order unpaid and check the UUID again later
    }
} catch (EsewaException $e) {
    // Invalid callback or failed status check: do NOT fulfil the order
    error_log($e->getMessage());
}
```

`verifyPayment()` decodes the base64 response and checks that:

- `signed_field_names` lists `transaction_code`, `status`, `total_amount`, `transaction_uuid` and `product_code`. The list may also include `signed_field_names` itself.
- The HMAC-SHA256 signature, rebuilt from the received values in the listed order, matches. The comparison uses timing-safe `hash_equals()`.
- `product_code` matches the configured product code.

It throws `EsewaException` if any of these checks fails. It does **not** check the `status` value, look up your order, or make a network request. Your application handles those steps as shown above.

### **Check Transaction Status**

`checkStatus()` asks eSewa for the transaction status, server to server. Use it to confirm every payment before fulfilment, and for background jobs or reconciliation that re-check payments left `PENDING` or `AMBIGUOUS`.

Status requests go to `https://rc.esewa.com.np` (test) and `https://esewa.com.np` (production). These hosts differ from the checkout form hosts returned by `getFormActionUrl()`.

```php
use RemoteMerge\Esewa\EsewaFactory;
use RemoteMerge\Esewa\Exceptions\EsewaException;

$epay = EsewaFactory::createEpay([/* config */]);

try {
    $status = $epay->checkStatus(
        transactionUuid: 'your-order-uuid',
        totalAmount: 665.00,
    );

    echo $status['status']; // "COMPLETE", "PENDING", "AMBIGUOUS", etc. (see the table below)
} catch (EsewaException $e) {
    echo $e->getMessage();
}
```

`checkStatus()` throws `EsewaException` when eSewa reports an error instead of a status. This includes a non-zero `code`, and any response with an `error_message`, such as `{"code": 0, "error_message": "Service is currently unavailable"}`.

**Transaction Status Values:**

| Status           | Description                                   |
|------------------|-----------------------------------------------|
| `COMPLETE`       | Payment completed successfully                |
| `PENDING`        | Payment initiated but not yet completed       |
| `FULL_REFUND`    | Complete refund has been issued               |
| `PARTIAL_REFUND` | Partial refund has been issued                |
| `AMBIGUOUS`      | Payment is in a halt state                    |
| `NOT_FOUND`      | Transaction session expired or does not exist |
| `CANCELED`       | Payment was reversed by eSewa                 |

---

## **Advanced Usage**

### **Manual Signature Verification**

`verifySignature()` checks a signature over the fields listed in a payload's `signed_field_names`, for example to check outgoing form data or a payload you stored. It returns a boolean and does not throw exceptions. Unlike `verifyPayment()`, it does not require the payment result fields to be signed or check the product code, so use `verifyPayment()` for eSewa callbacks.

```php
$isValid = $epay->verifySignature($responseData, $responseData['signature']);

if ($isValid) {
    // Payload is authentic
}
```

### **Custom HTTP Client**

The SDK accepts a custom HTTP client for cases where you need proxy configuration, custom timeouts, or request logging. Implement the `HttpClientInterface` and pass it as the second argument to the factory.

```php
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\EsewaFactory;

class CustomHttpClient implements HttpClientInterface
{
    public function get(string $url, array $headers = []): string
    {
        // Your custom implementation
    }

    public function post(string $url, array $data, array $headers = []): string
    {
        // Your custom implementation
    }
}

$epay = EsewaFactory::createEpay([/* config */], new CustomHttpClient());
```

### **Accessing Environment Details**

The SDK exposes a few helper methods for inspecting the current configuration:

```php
$epay->getEnvironment();    // "test" or "production"
$epay->getProductCode();    // Your configured product code
$epay->getFormActionUrl();  // eSewa form submission URL for current environment
```

---

## **Error Handling**

All SDK methods throw `EsewaException` on failure. Wrap every call that interacts with eSewa in a `try/catch` block. A successful `verifyPayment()` call alone does not confirm a payment. Fulfil an order only after the full [verify and confirm](#verify-and-confirm-a-payment) flow returns `COMPLETE`.

```php
use RemoteMerge\Esewa\Exceptions\EsewaException;

try {
    $payment = $epay->verifyPayment($encodedResponse);
    $status = $epay->checkStatus($order['uuid'], $order['total_amount']);
} catch (EsewaException $e) {
    error_log($e->getMessage());
    // Do NOT mark the order paid, show a user-friendly error instead
}
```

### **Common Exceptions**

**Configuration Errors:**

| Message                                              | Cause                            |
|------------------------------------------------------|----------------------------------|
| `Product code is required.`                          | Missing `product_code` in config |
| `Product code cannot be empty.`                      | Empty `product_code` string      |
| `Secret key is required.`                            | Missing `secret_key` in config   |
| `Secret key cannot be empty.`                        | Empty `secret_key` string        |
| `Success URL is required.`                           | Missing `success_url` in config  |
| `Failure URL is required.`                           | Missing `failure_url` in config  |
| `Environment must be either "test" or "production".` | Invalid environment value        |

**Payment Errors:**

| Message                                                               | Cause                              |
|-----------------------------------------------------------------------|------------------------------------|
| `Amount must be greater than 0.`                                      | Payment amount is zero or negative |
| `Transaction UUID must be alphanumeric and may contain hyphens only.` | UUID contains invalid characters   |
| `Properties must be an array.`                                        | `properties` is not an array       |
| `Properties cannot contain more than 3 entries.`                      | More than three custom properties  |
| `Property names must be non-empty strings without square brackets.`   | Invalid property name              |
| `Property values must be strings or numbers: {name}`                  | Invalid property value             |

**Verification Errors:**

| Message                                                                         | Cause                                               |
|---------------------------------------------------------------------------------|-----------------------------------------------------|
| `Invalid signature in response.`                                                | Signature mismatch, possible tampering              |
| `Failed to decode response data.`                                               | `data` parameter is not valid base64                |
| `Invalid response: missing signature or signed fields.`                         | Malformed eSewa response payload                    |
| `Invalid response: unsigned payment fields: {field_names}`                      | Payment result fields are not covered by signature  |
| `Invalid response: signed field names must be a comma-separated string.`        | Malformed `signed_field_names`                      |
| `Invalid response: signed field names contain an empty entry.`                  | Malformed `signed_field_names`                      |
| `Invalid response: product code does not match the configured product code.`    | Payload belongs to a different merchant             |
| `Missing signed field: {field_name}`                                            | A listed signed field is absent                     |
| `Invalid signed field value: {field_name}`                                      | A signed field holds an array or object             |

**API and HTTP Errors:**

| Message                     | Cause                                                               |
|-----------------------------|---------------------------------------------------------------------|
| `API Error: {message}`      | Status response has an `error_message` or a non-zero `code`         |
| `HTTP Error: {status_code}` | eSewa API returned HTTP 4xx/5xx                                     |
| `cURL Error: {error}`       | Network or connection failure                                       |

---

## **Security**

### **HMAC-SHA256 Signature Flow**

The SDK uses RFC 2104 HMAC with SHA256 for all cryptographic operations:

1. **Outgoing requests**: `createPayment()` signs `total_amount`, `transaction_uuid`, and `product_code` as a comma-separated `field=value` string and encodes the resulting hash in base64.
2. **Incoming responses**: `verifyPayment()` requires the payment result fields (`transaction_code`, `status`, `total_amount`, `transaction_uuid`, `product_code`) to be listed in `signed_field_names`, reconstructs the signed string from the received values, computes the expected signature, and compares it using timing-safe `hash_equals()` to prevent timing attacks. Requiring these fields stops the checkout request signature, which the customer can read from the form, from being replayed as a payment result.

### **Best Practices**

- **Never skip verification.** Always call `verifyPayment()` on your `success_url` handler.
- **Confirm before fulfilling.** Fulfil an order only after `checkStatus()` returns `COMPLETE` for your own UUID and total.
- **Protect your secret key.** Store it in environment variables or a secrets manager, never commit it to source code.
- **Use HTTPS in production.** Ensure `success_url` and `failure_url` use HTTPS to prevent response interception.
- **Validate amounts server-side.** Cross-check the `total_amount` from eSewa against your own order records to prevent amount manipulation.
- **Fulfil once.** Make the paid transition atomic and idempotent, because callbacks, refreshes and status checks can repeat.

---

## **Try with Docker**

The repository includes a demo store that can be run locally using Docker. This is the fastest way to see the SDK in action with eSewa sandbox credentials.

1. Clone the repository:

   ```bash
   git clone git@github.com:remotemerge/esewa-php-sdk.git
   cd esewa-php-sdk
   ```

2. Start the Docker container:

   ```bash
   docker compose up -d
   ```

3. Access the demo store at `http://localhost:8080`.

The demo store displays sample products and walks through the complete eSewa checkout flow using sandbox credentials. Its success page verifies the callback, matches it against the order in the session, and confirms it with `checkStatus()` before showing the payment as successful. The demo has no order database, so its protection against fulfilling an order twice is illustrative only.

---

## **Getting Help**

Bugs and feature requests are tracked using GitHub issues.

- **Bug Reports**
  Issues can be reported by [opening an issue](https://github.com/remotemerge/esewa-php-sdk/issues/new) on GitHub.

- **eSewa Account or Merchant Issues**
  For questions about eSewa accounts, merchant setup, or the eSewa API itself, contact [eSewa] directly.

- **API Reference**
  The official eSewa API documentation is available at the [eSewa Developer Portal].

---

## **Contributing**

Contributions from the open source community are welcome and appreciated. To maintain quality across the codebase, please follow these guidelines:

- **Coding Standards**: Code must adhere to [PER Coding Style 3.0] standards. Run `vendor/bin/php-cs-fixer fix` before submitting.
- **Testing**: All submitted code must pass the full test suite. Run `vendor/bin/phpunit` to verify.
- **Pull Requests**: Keep pull requests focused on a single change and submit against the `main` branch.

[eSewa]: https://esewa.com.np

[remotemerge]: https://github.com/remotemerge

[eSewa Developer Portal]: https://developer.esewa.com.np

[eSewa Merchant Dashboard]: https://merchant.esewa.com.np

[PER Coding Style 3.0]: https://www.php-fig.org/per/coding-style/