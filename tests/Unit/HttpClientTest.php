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

    private static $serverProcess;

    private HttpClient $httpClient;

    public static function setUpBeforeClass(): void
    {
        // Let the server bind the port and retry on a fresh one if it fails, avoiding the probe-and-release race.
        $lastError = '';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $port = random_int(20000, 65000);
            if (self::startServer($port, $lastError)) {
                self::$baseUrl = 'http://127.0.0.1:' . $port;
                return;
            }
        }

        throw new RuntimeException('Test server failed to start: ' . $lastError);
    }

    /**
     * Starts the built-in server on the given port and waits until it accepts connections.
     * Returns false (and the captured stderr in $error) if the port could not be bound.
     */
    private static function startServer(int $port, string &$error): bool
    {
        // Drain stdout/stderr to temp files: unread pipes can fill and stall the server, and temp files
        // are portable, unlike /dev/null which proc_open cannot open on Windows.
        $stdout = tmpfile();
        $stderr = tmpfile();
        // Disable JIT for the subprocess: under coverage it only emits a noisy, harmless startup warning.
        $command = ['php', '-d', 'opcache.jit=disable', '-S', '127.0.0.1:' . $port, __DIR__ . '/../Fixtures/server.php'];
        $descriptors = [['pipe', 'r'], $stdout, $stderr];
        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            $error = 'proc_open() failed';
            fclose($stdout);
            fclose($stderr);

            return false;
        }

        // Wait up to ~5 seconds for the server to accept connections; cold CI runners can be slow to boot.
        for ($i = 0; $i < 50; $i++) {
            // Confirm our fixture is answering, not a foreign listener that grabbed the port.
            if (self::pingFixture($port)) {
                self::$serverProcess = $process;
                fclose($stdout);
                fclose($stderr);

                return true;
            }

            // A dead process means the bind failed; let the caller retry on a different port.
            $status = proc_get_status($process);
            if (!$status['running']) {
                $error = self::readError($stderr);
                proc_close($process);
                fclose($stdout);
                fclose($stderr);

                return false;
            }

            usleep(100000);
        }

        // Running but unreachable after the timeout: tear it down and let the caller retry.
        proc_terminate($process);
        proc_close($process);
        $error = self::readError($stderr);
        fclose($stdout);
        fclose($stderr);

        return false;
    }

    /**
     * Extracts the meaningful failure line from the server's stderr, ignoring the startup banner and the
     * harmless JIT warning emitted under coverage.
     */
    private static function readError($stderr): string
    {
        rewind($stderr);
        foreach (explode("\n", (string) stream_get_contents($stderr)) as $line) {
            if (stripos($line, 'Failed to listen') !== false) {
                return trim($line);
            }
        }

        return 'server not reachable';
    }

    /**
     * Returns true only when the fixture server answers its /ok route, proving it is ours and ready.
     */
    private static function pingFixture(int $port): bool
    {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
        if ($socket === false) {
            return false;
        }

        $request = "GET /ok HTTP/1.1\r\nHost: 127.0.0.1\r\nConnection: close\r\n\r\n";
        fwrite($socket, $request);
        stream_set_timeout($socket, 1);
        $response = stream_get_contents($socket);
        fclose($socket);

        return is_string($response) && str_contains($response, '{"success":true}');
    }

    public static function tearDownAfterClass(): void
    {
        if (isset(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new HttpClient();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(HttpClientInterface::class, $this->httpClient);
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithoutHeaders(): void
    {
        $result = $this->httpClient->get(self::$baseUrl . '/ok');

        $this->assertSame('{"success":true}', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithHeaders(): void
    {
        $headers = ['X-Custom-Header' => 'TestValue'];

        $result = $this->httpClient->get(self::$baseUrl . '/headers', $headers);
        $decoded = json_decode($result, true);

        $this->assertSame('TestValue', $decoded['X-CUSTOM-HEADER']);
    }

    public function testGetThrowsOnHttpError(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 404');

        $this->httpClient->get(self::$baseUrl . '/error-404');
    }

    public function testGetThrowsOnCurlError(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error:');

        $this->httpClient->get('http://0.0.0.0:1/unreachable');
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithFormData(): void
    {
        $data = ['field1' => 'value1'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data);

        $this->assertSame('field1=value1', $result);
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithJsonData(): void
    {
        $data = ['amount' => 100];
        $headers = ['Content-Type' => 'application/json'];

        $result = $this->httpClient->post(self::$baseUrl . '/echo', $data, $headers);

        $this->assertSame('{"amount":100}', $result);
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

    public function testPostThrowsOnHttpError(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 500');

        $this->httpClient->post(self::$baseUrl . '/error-500', ['data' => 'test']);
    }

    public function testPostThrowsOnCurlError(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error:');

        $this->httpClient->post('http://0.0.0.0:1/unreachable', ['data' => 'test']);
    }
}
