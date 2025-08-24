<?php

declare(strict_types=1);

namespace Tests\Unit;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RemoteMerge\Esewa\Exceptions\EsewaException;
use RuntimeException;
use Throwable;

#[CoversClass(EsewaException::class)]
class EsewaExceptionTest extends TestCase
{
    public function testExceptionExtendsException(): void
    {
        $esewaException = new EsewaException();

        $this->assertInstanceOf(Exception::class, $esewaException);
        $this->assertInstanceOf(Throwable::class, $esewaException);
    }

    public function testExceptionWithDefaultValues(): void
    {
        $esewaException = new EsewaException();

        $this->assertSame('', $esewaException->getMessage());
        $this->assertEquals(0, $esewaException->getCode());
        $this->assertNotInstanceOf(Throwable::class, $esewaException->getPrevious());
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Payment validation failed';
        $esewaException = new EsewaException($message);

        $this->assertSame($message, $esewaException->getMessage());
        $this->assertEquals(0, $esewaException->getCode());
        $this->assertNotInstanceOf(Throwable::class, $esewaException->getPrevious());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'HTTP request failed';
        $code = 500;
        $esewaException = new EsewaException($message, $code);

        $this->assertSame($message, $esewaException->getMessage());
        $this->assertEquals($code, $esewaException->getCode());
        $this->assertNotInstanceOf(Throwable::class, $esewaException->getPrevious());
    }

    public function testExceptionWithAllParameters(): void
    {
        $message = 'Database connection failed';
        $code = 1001;
        $previousException = new Exception('Connection timeout');

        $esewaException = new EsewaException($message, $code, $previousException);

        $this->assertSame($message, $esewaException->getMessage());
        $this->assertEquals($code, $esewaException->getCode());
        $this->assertSame($previousException, $esewaException->getPrevious());
    }

    public function testExceptionWithNullMessage(): void
    {
        $esewaException = new EsewaException('');

        $this->assertSame('', $esewaException->getMessage());
    }

    public function testExceptionWithNegativeCode(): void
    {
        $esewaException = new EsewaException('Error message', -1);

        $this->assertEquals(-1, $esewaException->getCode());
    }

    public function testExceptionWithZeroCode(): void
    {
        $esewaException = new EsewaException('Error message', 0);

        $this->assertEquals(0, $esewaException->getCode());
    }

    public function testExceptionWithLargeCode(): void
    {
        $code = PHP_INT_MAX;
        $esewaException = new EsewaException('Error message', $code);

        $this->assertEquals($code, $esewaException->getCode());
    }

    public function testExceptionWithSpecialCharactersInMessage(): void
    {
        $message = 'Error: Invalid amount $100.50 for transaction #12345!';
        $esewaException = new EsewaException($message);

        $this->assertSame($message, $esewaException->getMessage());
    }

    public function testExceptionWithUnicodeMessage(): void
    {
        $message = 'Payment error: रुपैया payment failed';
        $esewaException = new EsewaException($message);

        $this->assertSame($message, $esewaException->getMessage());
    }

    public function testExceptionWithMultilineMessage(): void
    {
        $message = "Line 1: Payment validation failed\nLine 2: Invalid merchant code\nLine 3: Please check configuration";
        $esewaException = new EsewaException($message);

        $this->assertSame($message, $esewaException->getMessage());
    }

    public function testExceptionWithVeryLongMessage(): void
    {
        $message = str_repeat('This is a long error message. ', 100);
        $esewaException = new EsewaException($message);

        $this->assertSame($message, $esewaException->getMessage());
    }

    public function testExceptionWithChainedExceptions(): void
    {
        $rootException = new Exception('Root cause');
        $middleException = new Exception('Middle error', 0, $rootException);
        $esewaException = new EsewaException('Top level error', 500, $middleException);

        $this->assertSame('Top level error', $esewaException->getMessage());
        $this->assertEquals(500, $esewaException->getCode());
        $this->assertSame($middleException, $esewaException->getPrevious());
        $this->assertSame($rootException, $esewaException->getPrevious()->getPrevious());
    }

    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(EsewaException::class);
        $this->expectExceptionMessage('Test exception');
        $this->expectExceptionCode(123);

        throw new EsewaException('Test exception', 123);
    }

    public function testExceptionCanBeCaught(): void
    {
        $caught = false;
        $message = 'Caught exception';
        $code = 456;

        try {
            throw new EsewaException($message, $code);
        } catch (EsewaException $esewaException) {
            $caught = true;
            $this->assertSame($message, $esewaException->getMessage());
            $this->assertEquals($code, $esewaException->getCode());
        }

        $this->assertTrue($caught);
    }

    public function testExceptionCanBeCaughtAsException(): void
    {
        $caught = false;
        $message = 'Generic catch';

        try {
            throw new EsewaException($message);
        } catch (Exception $exception) {
            $caught = true;
            $this->assertInstanceOf(EsewaException::class, $exception);
            $this->assertSame($message, $exception->getMessage());
        }

        $this->assertTrue($caught);
    }

    public function testExceptionCanBeCaughtAsThrowable(): void
    {
        $caught = false;
        $message = 'Throwable catch';

        try {
            throw new EsewaException($message);
        } catch (Throwable $throwable) {
            $caught = true;
            $this->assertInstanceOf(EsewaException::class, $throwable);
            $this->assertSame($message, $throwable->getMessage());
        }

        $this->assertTrue($caught);
    }

    public function testExceptionToString(): void
    {
        $message = 'String conversion test';
        $code = 789;
        $esewaException = new EsewaException($message, $code);

        $stringRepresentation = (string) $esewaException;

        $this->assertStringContainsString($message, $stringRepresentation);
        $this->assertStringContainsString('EsewaException', $stringRepresentation);
        $this->assertIsString($stringRepresentation);
        $this->assertNotEmpty($stringRepresentation);
    }

    public function testExceptionFile(): void
    {
        $esewaException = new EsewaException('File test');

        $this->assertStringEndsWith('EsewaExceptionTest.php', $esewaException->getFile());
    }

    public function testExceptionLine(): void
    {
        $lineNumber = __LINE__ + 1;
        $esewaException = new EsewaException('Line test');

        $this->assertSame($lineNumber, $esewaException->getLine());
    }

    public function testExceptionTrace(): void
    {
        $esewaException = new EsewaException('Trace test');

        $trace = $esewaException->getTrace();
        $this->assertIsArray($trace);
        $this->assertNotEmpty($trace);
    }

    public function testExceptionTraceAsString(): void
    {
        $esewaException = new EsewaException('Trace string test');

        $traceString = $esewaException->getTraceAsString();
        $this->assertIsString($traceString);
        $this->assertStringContainsString(self::class, $traceString);
    }

    public function testExceptionWithPreviousExceptionOfDifferentType(): void
    {
        $runtimeException = new RuntimeException('Runtime error');
        $esewaException = new EsewaException('Esewa error', 0, $runtimeException);

        $this->assertSame($runtimeException, $esewaException->getPrevious());
        $this->assertInstanceOf(RuntimeException::class, $esewaException->getPrevious());
    }

    public function testExceptionWithNullPrevious(): void
    {
        $esewaException = new EsewaException('Test', 0, null);

        $this->assertNotInstanceOf(Throwable::class, $esewaException->getPrevious());
    }

    public function testMultipleExceptionInstances(): void
    {
        $exception1 = new EsewaException('First exception');
        $exception2 = new EsewaException('Second exception');

        $this->assertNotSame($exception1, $exception2);
        $this->assertSame('First exception', $exception1->getMessage());
        $this->assertSame('Second exception', $exception2->getMessage());
    }

    public function testExceptionSerialization(): void
    {
        $message = 'Serialization test';
        $code = 999;
        $esewaException = new EsewaException($message, $code);

        $serialized = serialize($esewaException);
        $unserializedException = unserialize($serialized);

        $this->assertInstanceOf(EsewaException::class, $unserializedException);
        $this->assertSame($message, $unserializedException->getMessage());
        $this->assertEquals($code, $unserializedException->getCode());
    }
}
