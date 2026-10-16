<?php

namespace Behatch\Exception;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Exception\ExpectationException;

/**
 * An expectation failure with a short message for the normal output.
 *
 * getMessage() returns the full message. TruncatableExceptionStringer prints the short
 * message when Behat runs without -v.
 */
final class TruncatableExpectationException extends ExpectationException
{
    private readonly string $shortMessage;

    public function __construct(string $message, DriverInterface $driver, string $shortMessage, ?\Throwable $exception = null)
    {
        $this->shortMessage = $shortMessage;

        parent::__construct($message, $driver, $exception);
    }

    public function getShortMessage(): string
    {
        return $this->shortMessage;
    }
}
