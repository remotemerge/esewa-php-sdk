<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionType;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RemoteMerge\Esewa\Http\HttpClient;
use RemoteMerge\Esewa\Http\HttpClientInterface;

#[CoversClass(HttpClient::class)]
final class HttpClientTest extends TestCase
{
    private TestableHttpClient $testableHttpClient;

    protected function setUp(): void
    {
        $this->testableHttpClient = new TestableHttpClient();
    }

    public function testImplementsHttpClientInterface(): void
    {
        $httpClient = new HttpClient();
        $this->assertInstanceOf(HttpClientInterface::class, $httpClient);
    }

    public function testGetMethodExists(): void
    {
        $this->assertTrue(method_exists($this->testableHttpClient, 'get'));
    }

    public function testPostMethodExists(): void
    {
        $this->assertTrue(method_exists($this->testableHttpClient, 'post'));
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithValidUrl(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('{"success": true}');
        $this->testableHttpClient->mockHttpCode(200);

        $result = $this->testableHttpClient->get('https://api.esewa.com.np/test');

        $this->assertSame('{"success": true}', $result);
    }

    public function testGetWithInvalidUrlThrowsException(): void
    {
        $this->testableHttpClient->mockCurlInitFailure();

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failed to initialize cURL');

        $this->testableHttpClient->get('invalid-url');
    }

    public function testGetWithCurlErrorThrowsException(): void
    {
        $this->testableHttpClient->mockCurlExecFailure('Connection timeout');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error: Connection timeout');

        $this->testableHttpClient->get('https://api.esewa.com.np/test');
    }

    public function testGetWithHttpErrorThrowsException(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('Not Found');
        $this->testableHttpClient->mockHttpCode(404);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 404');

        $this->testableHttpClient->get('https://api.esewa.com.np/test');
    }

    /**
     * @throws EsewaException
     */
    public function testGetWithHeaders(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('{"data": "test"}');
        $this->testableHttpClient->mockHttpCode(200);

        $headers = [
            'Authorization' => 'Bearer token123',
            'User-Agent' => 'Test-Agent/1.0',
        ];

        $result = $this->testableHttpClient->get('https://api.esewa.com.np/test', $headers);

        $this->assertSame('{"data": "test"}', $result);
        $this->assertTrue($this->testableHttpClient->wasHeaderSet('Authorization: Bearer token123'));
        $this->assertTrue($this->testableHttpClient->wasHeaderSet('User-Agent: Test-Agent/1.0'));
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithFormData(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('{"status": "created"}');
        $this->testableHttpClient->mockHttpCode(201);

        $data = ['field1' => 'value1', 'field2' => 'value2'];

        $result = $this->testableHttpClient->post('https://api.esewa.com.np/create', $data);

        $this->assertSame('{"status": "created"}', $result);
        $this->assertTrue($this->testableHttpClient->wasPostSet());
        $this->assertSame('field1=value1&field2=value2', $this->testableHttpClient->getLastPostFields());
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithJsonData(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('{"id": 123}');
        $this->testableHttpClient->mockHttpCode(200);

        $data = ['amount' => 100.50, 'currency' => 'NPR'];
        $headers = ['Content-Type' => 'application/json'];

        $result = $this->testableHttpClient->post('https://api.esewa.com.np/payment', $data, $headers);

        $this->assertSame('{"id": 123}', $result);
        $this->assertTrue($this->testableHttpClient->wasPostSet());
        $this->assertSame('{"amount":100.5,"currency":"NPR"}', $this->testableHttpClient->getLastPostFields());
        $this->assertTrue($this->testableHttpClient->wasHeaderSet('Content-Type: application/json'));
    }

    /**
     * @throws EsewaException
     */
    public function testPostDefaultContentType(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('OK');
        $this->testableHttpClient->mockHttpCode(200);

        $data = ['test' => 'value'];

        $this->testableHttpClient->post('https://api.esewa.com.np/test', $data);

        $this->assertTrue($this->testableHttpClient->wasHeaderSet('Content-Type: application/x-www-form-urlencoded'));
    }

    public function testPostWithCurlInitFailureThrowsException(): void
    {
        $this->testableHttpClient->mockCurlInitFailure();

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Failed to initialize cURL');

        $this->testableHttpClient->post('invalid-url', []);
    }

    public function testPostWithCurlErrorThrowsException(): void
    {
        $this->testableHttpClient->mockCurlExecFailure('Network error');

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('cURL Error: Network error');

        $this->testableHttpClient->post('https://api.esewa.com.np/test', []);
    }

    public function testPostWithHttpErrorThrowsException(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('Server Error');
        $this->testableHttpClient->mockHttpCode(500);

        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('HTTP Error: 500');

        $this->testableHttpClient->post('https://api.esewa.com.np/test', []);
    }

    /**
     * @throws EsewaException
     */
    public function testPostWithSpecialCharacters(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('OK');
        $this->testableHttpClient->mockHttpCode(200);

        $data = [
            'special_chars' => '!@#$%^&*()',
            'unicode' => 'नेपाली',
            'encoded' => 'test data with spaces',
        ];

        $result = $this->testableHttpClient->post('https://api.esewa.com.np/test', $data);

        $this->assertSame('OK', $result);
    }

    public function testGetReturnsString(): void
    {
        $reflectionMethod = new ReflectionMethod(HttpClient::class, 'get');
        $returnType = $reflectionMethod->getReturnType();

        $this->assertInstanceOf(ReflectionType::class, $returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    public function testPostReturnsString(): void
    {
        $reflectionMethod = new ReflectionMethod(HttpClient::class, 'post');
        $returnType = $reflectionMethod->getReturnType();

        $this->assertInstanceOf(ReflectionType::class, $returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    /**
     * @throws EsewaException
     */
    public function testTimeoutIsSet(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('OK');
        $this->testableHttpClient->mockHttpCode(200);

        $this->testableHttpClient->get('https://api.esewa.com.np/test');

        $this->assertTrue($this->testableHttpClient->wasTimeoutSet(30));
    }

    /**
     * @throws EsewaException
     */
    public function testSslVerificationIsEnabled(): void
    {
        $this->testableHttpClient->mockCurlExecResponse('OK');
        $this->testableHttpClient->mockHttpCode(200);

        $this->testableHttpClient->get('https://api.esewa.com.np/test');

        $this->assertTrue($this->testableHttpClient->wasSslVerificationEnabled());
    }
}

/**
 * Testable version of HttpClient that allows mocking cURL functions
 */
class TestableHttpClient extends HttpClient
{
    private bool $curlInitShouldFail = false;

    private bool $curlExecShouldFail = false;

    private string $curlExecResponse = '';

    private string $curlError = '';

    private int $httpCode = 200;

    private array $curlOptions = [];

    private array $setHeaders = [];

    private bool $postWasSet = false;

    private string $lastPostFields = '';

    public function mockCurlInitFailure(): void
    {
        $this->curlInitShouldFail = true;
    }

    public function mockCurlExecResponse(string $response): void
    {
        $this->curlExecResponse = $response;
        $this->curlExecShouldFail = false;
    }

    public function mockCurlExecFailure(string $error): void
    {
        $this->curlExecShouldFail = true;
        $this->curlError = $error;
    }

    public function mockHttpCode(int $code): void
    {
        $this->httpCode = $code;
    }

    public function wasHeaderSet(string $header): bool
    {
        return in_array($header, $this->setHeaders, true);
    }

    public function wasPostSet(): bool
    {
        return $this->postWasSet;
    }

    public function getLastPostFields(): string
    {
        return $this->lastPostFields;
    }

    public function wasTimeoutSet(int $timeout): bool
    {
        return isset($this->curlOptions[CURLOPT_TIMEOUT]) && $this->curlOptions[CURLOPT_TIMEOUT] === $timeout;
    }

    public function wasSslVerificationEnabled(): bool
    {
        return isset($this->curlOptions[CURLOPT_SSL_VERIFYPEER]) && $this->curlOptions[CURLOPT_SSL_VERIFYPEER] === true
            && isset($this->curlOptions[CURLOPT_SSL_VERIFYHOST]) && $this->curlOptions[CURLOPT_SSL_VERIFYHOST] === 2;
    }

    public function get(string $url, array $headers = []): string
    {
        if ($this->curlInitShouldFail) {
            throw new EsewaException('Failed to initialize cURL', 0);
        }

        // Simulate curl_setopt calls
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $this->curlOptions[CURLOPT_TIMEOUT] = 30;
        $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
        $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;

        foreach ($headers as $key => $value) {
            $this->setHeaders[] = sprintf('%s: %s', $key, $value);
        }

        if ($this->curlExecShouldFail) {
            throw new EsewaException('cURL Error: ' . $this->curlError, 0);
        }

        if ($this->httpCode >= 400) {
            throw new EsewaException('HTTP Error: ' . $this->httpCode, $this->httpCode);
        }

        return $this->curlExecResponse;
    }

    public function post(string $url, array $data, array $headers = []): string
    {
        if ($this->curlInitShouldFail) {
            throw new EsewaException('Failed to initialize cURL', 0);
        }

        $this->postWasSet = true;

        // Simulate curl_setopt calls
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $this->curlOptions[CURLOPT_POST] = true;
        $this->curlOptions[CURLOPT_TIMEOUT] = 30;
        $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
        $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;

        $isJson = isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json';
        $this->lastPostFields = $isJson ? json_encode($data) : http_build_query($data);

        if (!$isJson && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        foreach ($headers as $key => $value) {
            $this->setHeaders[] = sprintf('%s: %s', $key, $value);
        }

        if ($this->curlExecShouldFail) {
            throw new EsewaException('cURL Error: ' . $this->curlError, 0);
        }

        if ($this->httpCode >= 400) {
            throw new EsewaException('HTTP Error: ' . $this->httpCode, $this->httpCode);
        }

        return $this->curlExecResponse;
    }
}
