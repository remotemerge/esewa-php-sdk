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
        $port = self::findFreePort();
        self::$baseUrl = 'http://127.0.0.1:' . $port;

        // Start PHP built-in server using proc_open (cross-platform)
        $command = ['php', '-S', '127.0.0.1:' . $port, __DIR__ . '/../Fixtures/server.php'];
        $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        self::$serverProcess = proc_open($command, $descriptors, $pipes);

        if (self::$serverProcess === false) {
            throw new RuntimeException('Failed to start test server');
        }

        // Wait up to 15 seconds for the server to accept connections; cold CI runners can be slow to boot.
        for ($i = 0; $i < 150; $i++) {
            // A successful connection means the server is ready.
            if (@fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1)) {
                return;
            }

            // Fail fast only if the server died, e.g., the port could not be bound. A running child
            // reports exitcode -1, so check for a real exit code to avoid aborting while it boots.
            $status = proc_get_status(self::$serverProcess);
            if (!$status['running'] && $status['exitcode'] > 0) {
                throw new RuntimeException('Test server process exited: ' . trim(stream_get_contents($pipes[2])));
            }

            usleep(100000);
        }

        throw new RuntimeException('Test server failed to start');
    }

    /**
     * Returns an available loopback port, allocated by the OS to avoid collisions on shared runners.
     */
    private static function findFreePort(): int
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new RuntimeException('Failed to allocate a free port: ' . $errstr);
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr((string) $name, strrpos((string) $name, ':') + 1);
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
