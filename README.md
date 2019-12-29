# eSewa SDK for PHP

[![@cixware on Twitter](https://img.shields.io/badge/Twitter-%40cixware-blue.svg?style=flat&logo=twitter)](https://twitter.com/cixware)
[![@cixware on Facebook](https://img.shields.io/badge/Facebok-%40cixware-blue.svg?style=flat&logo=facebook)](https://www.facebook.com/cixware)
[![Downloads](https://img.shields.io/packagist/dt/cixware/esewa-php-sdk.svg?style=flat&label=Downloads)](https://packagist.org/packages/cixware/esewa-php-sdk)
[![Build](https://img.shields.io/travis/cixware/esewa-php-sdk.svg?style=flat&logo=travis&label=Build)](https://travis-ci.org/cixware/esewa-php-sdk)
[![Apache 2 License](https://img.shields.io/packagist/l/cixware/esewa-php-sdk.svg?style=flat&label=License)](https://www.apache.org/licenses/LICENSE-2.0)

The **eSewa SDK for PHP** makes it easy for developers to access [eSewa] payment service in their PHP code. This repo and the package is developed and maintained by [Cixware]. You can find more details in [eSewa Documentation] site.

# Getting Started
1. **Sign up for eSewa** – Before you begin, you need to sign up and retrieve your credentials from [eSewa].
2. **Minimum requirements** – To run the SDK, your system will need to meet the minimum requirements, including having **PHP >= 7.2**. We highly recommend having it compiled with the cURL extension and cURL compiled with a TLS backend (e.g., NSS or OpenSSL). We do not support outdated PHP versions.

# Installation
**Install the SDK** – Using [Composer] is the recommended way to install the eSewa SDK for PHP. The SDK is available via [Packagist] under the [`cixware/esewa-php-sdk`][install-packagist] package.
```
composer require cixware/esewa-php-sdk
```

## Getting Help
We use the GitHub issues for tracking bugs and feature requests and address them as quickly as possible.

* Call or Email [eSewa] for account and payment related queries.
* If it turns out that you may have found a bug, please [open an issue](https://github.com/cixware/esewa-php-sdk/issues/new).
* For further development and integration in your application contact [Cixware].

[eSewa]: https://esewa.com.np
[eSewa Documentation]: https://developer.esewa.com.np
[eSewa Contact]: https://blog.esewa.com.np/contact-us/

[Cixware]: https://cixware.io

[composer]: http://getcomposer.org
[packagist]: http://packagist.org
[install-packagist]: https://packagist.org/packages/cixware/esewa-php-sdk