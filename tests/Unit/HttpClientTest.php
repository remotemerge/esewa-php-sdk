<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use RemoteMerge\Esewa\Contracts\HttpClientInterface;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClient;
use RuntimeException;
use Tests\ParentTestCase;

#[CoversClass(HttpClient::class)]
final class HttpClientTest extends ParentTestCase
{
    private static string $baseUrl;

    private static int $serverPid;

    private HttpClient $httpClient;

    public static function setUpBeforeClass(): void
    {
        $port = 18923;
        self::$baseUrl = 'http://127.0.0.1:' . $port;

        $serverScript = __DIR__ . '/../Fixtures/server.php';

        // Start the built-in PHP server in the background (cross-platform)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: use start command
            $command = sprintf(
                'start /B php -S 127.0.0.1:%d %s',
                $port,
                escapeshellarg($serverScript),
            );
            exec($command);
            // Give Windows extra time to start
            usleep(500000);
        } else {
            // Unix/Linux/macOS: use & with PID capture
            $command = sprintf(
                'php -S 127.0.0.1:%d %s > /dev/null 2>&1 & echo $!',
                $port,
                escapeshellarg($serverScript),
            );
            $output = shell_exec($command);
            self::$serverPid = $output !== null ? (int) trim($output) : 0;
        }

        // Wait for server to be ready (up to 10 seconds)
        $maxAttempts = 100;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
            if ($connection !== false) {
                fclose($connection);
                return;
            }

            usleep(100000); // 100ms
        }

        throw new RuntimeException(
            sprintf('Failed to start test server on port %d after %d attempts.', $port, $maxAttempts),
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: kill PHP process by pattern
            exec('taskkill /F /FI "WINDOWTITLE eq php*" 2>nul');
        } elseif (isset(self::$serverPid) && self::$serverPid > 0 && function_exists('posix_kill')) {
            // Unix/Linux/macOS: kill by PID
            posix_kill(self::$serverPid, SIGTERM);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new HttpClient();
    }

    public function testImplementsHttpClientInterface(): void
    {
        $this->assertInstanceOf(HttpClientInterface::class, $this->httpClient);
    }

    /**
     * @throws EsewaException
     */
    public function testGetReturnsResponseBody(): void
    {
        $result = $this->httpClient->get(self::$baseUrl . '/ok');

        $this->assertSame('{"success":true}', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithEmptyHeaders(): void
    {
        $result = $this->httpClient->get(self::$baseUrl . '/ok', []);

        $this->assertSame('{"success":true}', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithHeaders(): void
    {
        $headers = [
            'X-Custom-Header' => 'TestValue',
            'X-Another' => 'AnotherValue',
        ];

        $result = $this->httpClient->get(self::$baseUrl . '/headers', $headers);
        $decoded = json_decode($result, true);

        $this->assertSame('TestValue', $decoded['X-CUSTOM-HEADER']);
        $this->assertSame('AnotherValue', $decoded['X-ANOTHER']);
    }

    public function testGetWithHttpErrorThrowsException(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 404');

        $this->httpClient->get(self::$baseUrl . '/error-404');
    }

    public function testGetWithServerErrorThrowsException(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 500');

        $this->httpClient->get(self::$baseUrl . '/error-500');
    }

    public function testGetWithCurlErrorThrowsException(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error:');

        // Use a protocol that curl cannot resolve to trigger a curl error
        $this->httpClient->get('http://0.0.0.0:1/unreachable');
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithFormData(): void
    {
        $data = ['field1' => 'value1', 'field2' => 'value2'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data);

        $this->assertSame('field1=value1&field2=value2', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithJsonData(): void
    {
        $data = ['amount' => 100, 'currency' => 'NPR'];
        $headers = ['Content-Type' => 'application/json'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data, $headers);

        $this->assertSame('{"amount":100,"currency":"NPR"}', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testPostDefaultContentType(): void
    {
        $data = ['test' => 'value'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data);

        $this->assertSame('test=value', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithExplicitFormContentType(): void
    {
        $data = ['key' => 'val'];
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data, $headers);

        $this->assertSame('key=val', $result);
    }

    public function testPostWithHttpErrorThrowsException(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 500');

        $this->httpClient->post(self::$baseUrl . '/error-500', ['data' => 'test']);
    }

    public function testPostWithCurlErrorThrowsException(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error:');

        $this->httpClient->post('http://0.0.0.0:1/unreachable', ['data' => 'test']);
    }
}
