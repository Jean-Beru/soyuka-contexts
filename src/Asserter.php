<?php

namespace Behatch;

use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

trait Asserter
{
    protected function not(callable $callbable, string $errorMessage): void
    {
        try {
            $callbable();
        } catch (\LogicException|UnsupportedDriverActionException $e) {
            // a missing package or an unsupported driver cannot prove the negation
            throw $e;
        } catch (\Exception $e) {
            return;
        }

        throw new ExpectationException($errorMessage, $this->getSession()->getDriver());
    }

    protected function assert(bool $test, string $message): void
    {
        if (false === $test) {
            throw new ExpectationException($message, $this->getSession()->getDriver());
        }
    }

    protected function assertContains(string $expected, string $actual, ?string $message = null): void
    {
        $regex = '/'.preg_quote($expected, '/').'/ui';

        $this->assert(
            preg_match($regex, $actual) > 0,
            $message ?: "The string '$expected' was not found."
        );
    }

    protected function assertNotContains(string $expected, string $actual, ?string $message = null): void
    {
        $message = $message ?: "The string '$expected' was found.";

        $this->not(function () use ($expected, $actual): void {
            $this->assertContains($expected, $actual);
        }, $message);
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
