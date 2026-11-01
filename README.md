# eSewa PHP SDK

[![PHP Version](https://img.shields.io/packagist/php-v/remotemerge/esewa-php-sdk?style=flat&logo=php&label=php)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![Packagist](https://img.shields.io/packagist/v/remotemerge/esewa-php-sdk?style=flat&logo=packagist&label=packagist)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![Sonar Quality](https://img.shields.io/sonar/quality_gate/esewa-php-sdk/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubecloud&logoColor=126ED3&label=quality)](https://sonarcloud.io/summary/overall?id=esewa-php-sdk&branch=main)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/esewa-php-sdk/main?server=https%3A%2F%2Fsonarcloud.io&style=flat&logo=sonarqubecloud&logoColor=126ED3&label=coverage)](https://sonarcloud.io/summary/overall?id=esewa-php-sdk&branch=main)
[![Downloads](https://img.shields.io/packagist/dt/remotemerge/esewa-php-sdk?style=flat&logo=packagist&label=downloads)](https://packagist.org/packages/remotemerge/esewa-php-sdk)
[![License](https://img.shields.io/github/license/remotemerge/esewa-php-sdk?style=flat&label=license&logo=opensourceinitiative&logoColor=fff)](LICENSE)

Accept [eSewa] payments in PHP. This library implements the eSewa ePay v2 API. It signs the checkout request, verifies the signed callback eSewa sends after a payment, and confirms the transaction with the eSewa status API.

```php
use RemoteMerge\Esewa\EsewaFactory;

$epay = EsewaFactory::createEpay([
    'environment'  => 'test',
    'product_code' => 'EPAYTEST',
    'secret_key'   => '8gBm/:&EnhH.1/q',
    'success_url'  => 'https://example.com/success.php',
    'failure_url'  => 'https://example.com/failed.php',
]);

$form = $epay->createPayment(['amount' => 100, 'transaction_uuid' => 'order-123']); // signed form fields
$payment = $epay->verifyPayment($_GET['data'] ?? ''); // on the success_url page
$status = $epay->checkStatus('order-123', 100.0); // ['status' => 'COMPLETE', ...] once paid
```

- Signs checkout requests with HMAC-SHA256 and calculates `total_amount` from its parts.
- Verifies callbacks with timing-safe `hash_equals()`, requires the payment result fields to be signed, and checks the product code.
- Confirms transactions server to server with the eSewa status API.
- Validates configuration and payment input, and throws `EsewaException` with a specific message for each failure.
- Supports the eSewa test and production environments, and accepts a custom HTTP client.
- Needs no framework and has no runtime dependencies beyond `ext-curl` and `ext-json`.

## Installation

```sh
composer require remotemerge/esewa-php-sdk
```

The package requires PHP 8.1 or later with the `curl` and `json` extensions.

## Usage

A payment has four steps: create a signed form, send the customer to eSewa, verify the callback, and confirm the transaction with eSewa before fulfilling the order.

### Configuration

Create an instance with `EsewaFactory::createEpay()`. It validates the options and returns an `EpayInterface`. eSewa publishes fixed credentials for the `test` environment, where no real money moves:

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

To pay at the eSewa test checkout, use eSewa ID `9806800001` to `9806800005`, password `Nepal@123`, MPIN `1122`, and OTP `123456`. See the [eSewa test credentials] for the full list.

| Option         | Required | Default | Description                                        |
| -------------- | -------- | ------- | -------------------------------------------------- |
| `environment`  | No       | `test`  | `test` or `production`                             |
| `product_code` | Yes      | -       | Merchant product code assigned by eSewa            |
| `secret_key`   | Yes      | -       | Secret key used to generate HMAC-SHA256 signatures |
| `success_url`  | Yes      | -       | Redirect URL after a successful payment            |
| `failure_url`  | Yes      | -       | Redirect URL after a failed or cancelled payment   |

### Creating a payment

Call `createPayment()` with the order details. It calculates `total_amount` as `amount + tax_amount + product_service_charge + product_delivery_charge` and signs the request. Use a unique `transaction_uuid` for every order.

```php
use RemoteMerge\Esewa\Exceptions\EsewaException;

try {
    $paymentData = $epay->createPayment([
        'amount'                  => 500.00,
        'tax_amount'              => 65.00,
        'product_service_charge'  => 0,
        'product_delivery_charge' => 100,
        'transaction_uuid'        => bin2hex(random_bytes(16)),
    ]);
} catch (EsewaException $e) {
    echo $e->getMessage();
}
```

| Field                     | Required | Description                                     |
| ------------------------- | -------- | ----------------------------------------------- |
| `amount`                  | Yes      | Base price, greater than 0                      |
| `transaction_uuid`        | Yes      | Unique order ID of letters, digits, and hyphens |
| `tax_amount`              | No       | Tax on the order (default `0`)                  |
| `product_service_charge`  | No       | Service charge (default `0`)                    |
| `product_delivery_charge` | No       | Delivery charge (default `0`)                   |
| `properties`              | No       | Up to three custom fields, described below      |

### Custom properties

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

- Names must be non-empty strings without `[` or `]`.
- Values may be `string`, `int`, or `float`, and `createPayment()` casts them to string. `bool`, `null`, arrays, and objects throw `EsewaException`.
- `createPayment()` does not sign properties, so the signature and `signed_field_names` are the same with or without them.
- eSewa does not document whether properties are returned in the callback, so do not rely on reading them back.

### Rendering the payment form

Render the returned fields as hidden inputs and submit the form to `getFormActionUrl()`. eSewa shows its checkout page and then redirects the customer to `success_url` or `failure_url`.

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

### Verifying and confirming a payment

After a payment, eSewa redirects to `success_url` with a `data` query parameter. It holds a base64-encoded JSON payload that eSewa signs. A valid signature proves the payload came from eSewa, but it is not enough to mark the order paid. eSewa requires merchants to confirm the transaction with a status request, and to stop delivery when the confirmed amount differs from the requested amount.

Mark an order paid only after all of these steps succeed:

1. **Verify** the callback with `verifyPayment()`.
2. **Match** `transaction_uuid` and `total_amount` against your own order record. eSewa may format the amount, for example as `"1,000.0"`, so strip the formatting only after verification and compare exact values, such as integer paisa, rather than floats.
3. **Confirm** with `checkStatus()`, using the UUID and total from your order record.
4. **Accept** only the `COMPLETE` status. Treat `PENDING`, `AMBIGUOUS`, any other status, a mismatch, or an exception as unconfirmed, and keep the UUID so you can check it again later.
5. **Fulfil** the order once. Make the paid transition atomic and idempotent, so refreshes, repeated callbacks, or repeated status checks cannot fulfil the same order twice. For example, run `UPDATE orders SET status = 'paid' WHERE uuid = ? AND status = 'pending'` and fulfil only when it changes one row.

```php
use RemoteMerge\Esewa\Exceptions\EsewaException;

function toPaisa(string|int|float $amount): int
{
    return (int) round((float) str_replace(',', '', (string) $amount) * 100);
}

$encodedResponse = $_GET['data'] ?? null;
if ($encodedResponse === null) {
    exit('No eSewa response');
}

try {
    // 1. Verify that eSewa signed the payload for this merchant
    $payment = $epay->verifyPayment($encodedResponse);

    // 2. Match the payload against your own order record
    $order = findOrderByUuid($payment['transaction_uuid']); // Your own lookup
    if ($order === null || toPaisa($payment['total_amount']) !== toPaisa($order['total_amount'])) {
        exit('Payment does not match the order'); // Do NOT fulfil
    }

    // 3. Confirm with eSewa, using your own UUID and total
    $status = $epay->checkStatus($order['uuid'], $order['total_amount']);

    // 4. Accept only COMPLETE, and 5. fulfil once
    if (($status['status'] ?? null) === 'COMPLETE') {
        markOrderPaidOnce($order['uuid']); // Your own atomic, idempotent update
    } else {
        // Not confirmed yet. Keep the order unpaid and check the UUID again later
    }
} catch (EsewaException $e) {
    // Invalid callback or failed status check. Do NOT fulfil the order
    error_log($e->getMessage());
}
```

### Checking transaction status

`checkStatus()` asks eSewa for the status of a transaction, server to server. Use it to confirm every payment before fulfilment, and in background jobs that re-check payments left `PENDING` or `AMBIGUOUS`.

```php
$status = $epay->checkStatus(
    transactionUuid: 'your-order-uuid',
    totalAmount: 665.00,
);

echo $status['status']; // 'COMPLETE', 'PENDING', 'AMBIGUOUS', ...
```

| Status           | Meaning                                       |
| ---------------- | --------------------------------------------- |
| `COMPLETE`       | Payment completed successfully                |
| `PENDING`        | Payment initiated but not yet completed       |
| `FULL_REFUND`    | eSewa issued a full refund                    |
| `PARTIAL_REFUND` | eSewa issued a partial refund                 |
| `AMBIGUOUS`      | Payment is in a halt state                    |
| `NOT_FOUND`      | Transaction session expired or does not exist |
| `CANCELED`       | eSewa reversed the payment                    |

### Handling errors

Every method except `verifySignature()` and the getters throws `EsewaException` on failure. Wrap calls that touch eSewa data in a `try` block, and never mark an order paid from the `catch` branch:

```php
use RemoteMerge\Esewa\Exceptions\EsewaException;

try {
    $payment = $epay->verifyPayment($encodedResponse);
    $status = $epay->checkStatus($order['uuid'], $order['total_amount']);
} catch (EsewaException $e) {
    error_log($e->getMessage());
    // Do NOT mark the order paid; show a friendly error instead
}
```

For HTTP errors, `$e->getCode()` holds the HTTP status code. See [Errors](#errors) for every message.

## API

### `EsewaFactory::createEpay(array $options, ?HttpClientInterface $httpClient = null): EpayInterface`

Validates `$options` (see [Configuration](#configuration)) and returns a configured instance. Pass `$httpClient` to replace the built-in cURL client.

Throws if a required option is missing or empty, or `environment` is not `test` or `production`.

### `createPayment(array $paymentData): array`

Returns the form fields to submit to eSewa: `amount`, `tax_amount`, `total_amount`, `transaction_uuid`, `product_code`, `product_service_charge`, `product_delivery_charge`, `success_url`, `failure_url`, `signed_field_names`, `signature`, and any `properties[name]` fields.

Throws if `amount` is missing or not greater than 0, `transaction_uuid` is missing or contains characters other than letters, digits, and hyphens, or `properties` is invalid.

### `verifyPayment(string $encodedResponse): array`

Decodes the `data` parameter from the eSewa callback and returns the payload. It checks that:

- `signed_field_names` lists `transaction_code`, `status`, `total_amount`, `transaction_uuid`, and `product_code`. The list may also include `signed_field_names` itself.
- The HMAC-SHA256 signature, rebuilt from the received values in the listed order, matches. The comparison uses timing-safe `hash_equals()`.
- `product_code` matches the configured product code.

Throws if any check fails. It does not check the `status` value, look up your order, or make a network request. Your application handles those steps as shown in [Verifying and confirming a payment](#verifying-and-confirming-a-payment).

### `checkStatus(string $transactionUuid, float $totalAmount): array`

Requests the transaction status from eSewa and returns the decoded JSON response.

Throws on a network failure, an HTTP status of 400 or higher, or a response that is not a JSON object. It also throws when the response has an `error_message`, such as `{"code": 0, "error_message": "Service is currently unavailable"}`, or a non-zero `code`.

### `verifySignature(array $data, string $signature): bool`

Checks a signature over the fields listed in `$data['signed_field_names']`, for example to check outgoing form data or a payload you stored. It returns `false` instead of throwing. Unlike `verifyPayment()`, it does not require the payment result fields to be signed or check the product code, so use `verifyPayment()` for eSewa callbacks.

### `getFormActionUrl()`, `getEnvironment()`, `getProductCode()`

```php
$epay->getFormActionUrl(); // eSewa checkout form URL for the current environment
$epay->getEnvironment(); // 'test' or 'production'
$epay->getProductCode(); // the configured product code
```

### `HttpClientInterface`

Implement this interface to add a proxy, custom timeouts, or request logging, and pass the client to `createEpay()`. Throw `EsewaException` when a request fails.

```php
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\EsewaFactory;

final class CustomHttpClient implements HttpClientInterface
{
    public function get(string $url, array $headers = []): string
    {
        // Your implementation
    }

    public function post(string $url, array $data, array $headers = []): string
    {
        // Your implementation
    }
}

$epay = EsewaFactory::createEpay([/* config */], new CustomHttpClient());
```

The built-in client verifies TLS certificates and times out after 30 seconds.

### Errors

Configuration errors:

| Message                                              | Cause                  |
| ---------------------------------------------------- | ---------------------- |
| `Product code is required.`                          | Missing `product_code` |
| `Product code cannot be empty.`                      | Empty `product_code`   |
| `Secret key is required.`                            | Missing `secret_key`   |
| `Secret key cannot be empty.`                        | Empty `secret_key`     |
| `Success URL is required.`                           | Missing `success_url`  |
| `Failure URL is required.`                           | Missing `failure_url`  |
| `Environment must be either "test" or "production".` | Invalid `environment`  |

Payment errors:

| Message                                                               | Cause                             |
| --------------------------------------------------------------------- | --------------------------------- |
| `Amount is required.`                                                 | Missing `amount`                  |
| `Amount must be greater than 0.`                                      | Amount is zero or negative        |
| `Transaction UUID is required.`                                       | Missing `transaction_uuid`        |
| `Transaction UUID must be alphanumeric and may contain hyphens only.` | UUID contains invalid characters  |
| `Properties must be an array.`                                        | `properties` is not an array      |
| `Properties cannot contain more than 3 entries.`                      | More than three custom properties |
| `Property names must be non-empty strings without square brackets.`   | Invalid property name             |
| `Property values must be strings or numbers: {name}`                  | Invalid property value            |

Verification errors:

| Message                                                                      | Cause                                   |
| ---------------------------------------------------------------------------- | --------------------------------------- |
| `Failed to decode response data.`                                            | `data` is not valid base64              |
| `Invalid JSON in response: {error}`                                          | Decoded `data` is not valid JSON        |
| `Invalid JSON in response: expected an object.`                              | Decoded `data` is not a JSON object     |
| `Invalid response: missing signature or signed fields.`                      | Malformed payload                       |
| `Invalid response: signed field names must be a comma-separated string.`     | Malformed `signed_field_names`          |
| `Invalid response: signed field names contain an empty entry.`               | Malformed `signed_field_names`          |
| `Invalid response: unsigned payment fields: {field_names}`                   | Payment result fields are not signed    |
| `Missing signed field: {field_name}`                                         | A listed signed field is absent         |
| `Invalid signed field value: {field_name}`                                   | A signed field holds an array or object |
| `Invalid signature in response.`                                             | Signature mismatch, possible tampering  |
| `Invalid response: product code does not match the configured product code.` | Payload belongs to a different merchant |

Status and HTTP errors:

| Message                                      | Cause                                                |
| -------------------------------------------- | ---------------------------------------------------- |
| `API Error: {message}`                       | Response has an `error_message` or a non-zero `code` |
| `Invalid JSON response: {error}`             | Response is not valid JSON                           |
| `Invalid JSON response: expected an object.` | Response is not a JSON object                        |
| `HTTP Error: {status_code}`                  | eSewa returned HTTP 400 or higher                    |
| `cURL Error: {error}`                        | Network or connection failure                        |

## Environments

| Environment  | Checkout form                  | Status API                |
| ------------ | ------------------------------ | ------------------------- |
| `test`       | `https://rc-epay.esewa.com.np` | `https://rc.esewa.com.np` |
| `production` | `https://epay.esewa.com.np`    | `https://esewa.com.np`    |

For production, get the live `product_code` and `secret_key` from the [eSewa Merchant Dashboard] and set `environment` to `production`. Everything else stays the same. For questions about eSewa accounts, merchant setup, or the eSewa API itself, contact [eSewa] directly.

The library implements eSewa ePay v2 only. It does not store orders, track fulfilment, or retry status checks; your application handles those.

## How signing works

The library uses HMAC-SHA256 (RFC 2104) with your secret key and encodes each signature in base64.

- **Checkout requests.** `createPayment()` signs `total_amount`, `transaction_uuid`, and `product_code` as the comma-separated string `total_amount=...,transaction_uuid=...,product_code=...`.
- **Callbacks.** `verifyPayment()` rebuilds the same kind of string from the fields listed in `signed_field_names` and compares signatures with timing-safe `hash_equals()`. It also requires eSewa to sign the payment result fields. The customer can read the checkout request signature in the form, and this requirement stops anyone from replaying that signature as a payment result.

In production:

- Call `verifyPayment()` on every `success_url` request, and fulfil an order only after `checkStatus()` returns `COMPLETE` for your own UUID and total.
- Keep the secret key in an environment variable or a secrets manager, never in source code.
- Use HTTPS for `success_url` and `failure_url`.
- Compare the amount eSewa reports with your own order record.
- Make the paid transition atomic and idempotent, because callbacks, refreshes, and status checks can repeat.

## Demo store

The repository includes a demo store that runs the full checkout flow against the eSewa test environment. It requires Docker.

```sh
git clone git@github.com:remotemerge/esewa-php-sdk.git
cd esewa-php-sdk
bash start-docker.sh
```

Open `http://localhost:8080`. The success page verifies the callback, matches it against the order in the session, and confirms it with `checkStatus()` before showing the payment as successful. The demo has no order database, so its protection against fulfilling an order twice is illustrative only.

## Security

See [SECURITY](SECURITY.md) for supported versions and how to report vulnerabilities privately.

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) for setup instructions, required checks, and how to submit a pull request.

## Code of conduct

See [CODE OF CONDUCT](CODE_OF_CONDUCT.md) for community expectations and how to report concerns.

## License

eSewa PHP SDK is released under the MIT License. See [LICENSE](LICENSE).

[eSewa]: https://esewa.com.np
[eSewa test credentials]: https://developer.esewa.com.np/pages/Test-credentials
[eSewa Merchant Dashboard]: https://merchant.esewa.com.np