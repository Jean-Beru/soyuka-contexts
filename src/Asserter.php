<?php

namespace Behatch;

use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behatch\Exception\TruncatableExpectationException;

trait Asserter
{
    protected function not(callable $callbable, string $errorMessage, ?string $shortMessage = null): void
    {
        try {
            $callbable();
        } catch (MissingDependencyException|UnsupportedDriverActionException $e) {
            // a missing package or an unsupported driver cannot prove the negation
            throw $e;
        } catch (\Exception $e) {
            return;
        }

        throw $this->createExpectationException($errorMessage, $shortMessage);
    }

    /**
     * @param ?string $shortMessage printed instead of $message when Behat runs without -v
     */
    protected function assert(bool $test, string $message, ?string $shortMessage = null): void
    {
        if (false === $test) {
            throw $this->createExpectationException($message, $shortMessage);
        }
    }

    protected function assertContains(string $expected, string $actual, ?string $message = null, ?string $shortMessage = null): void
    {
        $regex = '/'.preg_quote($expected, '/').'/ui';

        $this->assert(
            preg_match($regex, $actual) > 0,
            $message ?: "The string '$expected' was not found.",
            $shortMessage
        );
    }

    protected function assertNotContains(string $expected, string $actual, ?string $message = null, ?string $shortMessage = null): void
    {
        $message = $message ?: "The string '$expected' was found.";

        $this->not(function () use ($expected, $actual): void {
            $this->assertContains($expected, $actual);
        }, $message, $shortMessage);
    }

    private function createExpectationException(string $message, ?string $shortMessage): ExpectationException
    {
        $driver = $this->getSession()->getDriver();

        if (null === $shortMessage || $shortMessage === $message) {
            return new ExpectationException($message, $driver);
        }

        return new TruncatableExpectationException($message, $driver, $shortMessage);
    }

    protected function assertCount(int $expected, array $elements, ?string $message = null): void
    {
        $this->assert(
            $expected === \count($elements),
            $message ?: \sprintf('%d elements found, but should be %d.', \count($elements), $expected)
        );
    }

    protected function assertEquals(mixed $expected, mixed $actual, ?string $message = null): void
    {
        $this->assert(
            $expected == $actual,
            $message ?: "The element '$actual' is not equal to '$expected'"
        );
    }

    protected function assertSame(mixed $expected, mixed $actual, ?string $message = null): void
    {
        $this->assert(
            $expected === $actual,
            $message ?: "The element '$actual' is not equal to '$expected'"
        );
    }

    protected function assertArrayHasKey(int|string $key, array|\ArrayAccess $array, ?string $message = null): void
    {
        $this->assert(
            isset($array[$key]),
            $message ?: "The array has no key '$key'"
        );
    }

    protected function assertArrayNotHasKey(int|string $key, array|\ArrayAccess $array, ?string $message = null): void
    {
        $message = $message ?: "The array has key '$key'";

        $this->not(function () use ($key, $array): void {
            $this->assertArrayHasKey($key, $array);
        }, $message);
    }

    protected function assertTrue(bool $value, string $message = 'The value is false'): void
    {
        $this->assert($value, $message);
    }

    protected function assertFalse(bool $value, string $message = 'The value is true'): void
    {
        $this->not(function () use ($value): void {
            $this->assertTrue($value);
        }, $message);
    }
}
