# Contributing

Changes to eSewa PHP SDK must preserve payment security and public API compatibility.

## Before you start

Search existing issues before opening a new one. For questions about eSewa accounts, merchant
setup, or the eSewa API itself, contact [eSewa](https://esewa.com.np) directly.

Report suspected vulnerabilities through the private process in [SECURITY.md](SECURITY.md). Do not
include sensitive reports in issues, discussions, or pull requests.

## Requirements

Development runs inside Docker. Use Docker `>=28.3` with Docker Compose. The package supports
PHP `>=8.1`, so code must remain compatible with PHP 8.1.

Build and start the development container:

```sh
bash start-docker.sh
```

Open a shell in the container, then install dependencies from the lockfile:

```sh
bash pkg-cli.sh
composer install
```

Run every command in this guide inside the container. To run a single command from the host, use
`docker exec esewa-demo-server-1 <command>`.

## Making changes

Keep each change focused. Preserve `EsewaFactory::createEpay()`, the interfaces in `src/Contracts`,
the configuration option keys, the returned array shapes, and `EsewaException` unless the change
explicitly requires otherwise.

Follow the [eSewa developer documentation](https://developer.esewa.com.np) for request and response
fields. Never weaken signature verification. Keep the timing-safe `hash_equals()` comparison and the
payment result fields that `verifyPayment()` requires in `signed_field_names`. Do not commit secret
keys or credentials other than the public eSewa test credentials.

Code must follow [PER Coding Style 3.0](https://www.php-fig.org/per/coding-style/) and declare
`strict_types=1`.

## Tests

Add tests for behavior changes and regression tests for bug fixes. Tests live in `tests/Unit`, and
each test class must declare `#[CoversClass]`. Cover failure paths such as invalid configuration,
tampered or missing signatures, missing signed fields, malformed responses, and HTTP errors.
`HttpClient` tests run against a local PHP server in `tests/Fixtures/server.php`. Do not replace
them with mocks alone. Never weaken assertions or change fixtures just to make a test pass.

Run `vendor/bin/phpunit`, `vendor/bin/php-cs-fixer fix --dry-run --diff`, and
`composer validate --strict` before every pull request. CI runs the tests on PHP 8.1 to 8.5 on
Linux, Windows, and macOS.

Available commands:

| Command                                        | What it does                                         |
| ---------------------------------------------- | ---------------------------------------------------- |
| `vendor/bin/phpunit`                           | Runs the tests and writes HTML and Clover coverage   |
| `vendor/bin/phpunit --no-coverage`             | Runs the tests without coverage reports              |
| `vendor/bin/php-cs-fixer fix --dry-run --diff` | Checks coding standards without changing files       |
| `vendor/bin/php-cs-fixer fix`                  | Applies coding standard fixes                        |
| `vendor/bin/rector process --dry-run`          | Shows suggested refactorings without changing files  |
| `composer validate --strict`                   | Validates `composer.json`                            |
| `composer audit --locked`                      | Checks locked dependencies for known vulnerabilities |

## Pull requests

Submit pull requests against the `main` branch. Explain the problem and how your change fixes it.
List the checks you ran and their results. Link the relevant issue when one exists. For changes
that follow the eSewa API, cite the relevant eSewa documentation.

Keep the diff limited to the requested change. Do not include `vendor/`, coverage or test reports,
debugging output, unrelated formatting, or temporary artifacts.