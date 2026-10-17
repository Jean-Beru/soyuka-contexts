<?php

namespace Behatch\Tests\Unit\Exception;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Exception\ExpectationException;
use Behatch\Exception\TruncatableExceptionStringer;
use Behatch\Exception\TruncatableExpectationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class TruncatableExceptionStringerTest extends TestCase
{
    private TruncatableExceptionStringer $stringer;

    private TruncatableExpectationException $exception;

    protected function setUp(): void
    {
        $this->stringer = new TruncatableExceptionStringer();
        $driver = $this->createConfiguredStub(DriverInterface::class, [
            'getContent' => '<html><body>The page</body></html>',
            'getCurrentUrl' => 'http://localhost/',
            'getStatusCode' => 200,
        ]);
        $this->exception = new TruncatableExpectationException('The full message', $driver, 'The short message');
    }

    public function testSupportsOnlyTruncatableExceptions(): void
    {
        $this->assertTrue($this->stringer->supportsException($this->exception));
        $this->assertFalse($this->stringer->supportsException(new ExpectationException('A message', $this->createStub(DriverInterface::class))));
    }

    public function testPrintsTheShortMessageWithoutVerbose(): void
    {
        $this->assertSame(
            'The short message (run with -v to see the full value) ('.TruncatableExpectationException::class.')',
            $this->stringer->stringException($this->exception, OutputInterface::VERBOSITY_NORMAL)
        );
    }

    public function testPrintsTheFullMessageWithVerbose(): void
    {
        $this->assertSame(
            'The full message ('.TruncatableExpectationException::class.')',
            $this->stringer->stringException($this->exception, OutputInterface::VERBOSITY_VERBOSE)
        );
    }

    public function testPrintsTheFullMessageWithVeryVerbose(): void
    {
        $this->assertSame(
            'The full message ('.TruncatableExpectationException::class.')',
            $this->stringer->stringException($this->exception, OutputInterface::VERBOSITY_VERY_VERBOSE)
        );
    }

    public function testPrintsTheMinkOutputWithDebug(): void
    {
        $this->assertStringContainsString('The page', (string) $this->exception);
        $this->assertSame(
            trim((string) $this->exception),
            $this->stringer->stringException($this->exception, OutputInterface::VERBOSITY_DEBUG)
        );
    }
}
