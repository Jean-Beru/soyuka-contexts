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
    public function __construct(string $message, DriverInterface $driver, private readonly string $shortMessage)
    {
        parent::__construct($message, $driver);
    }

    public function getShortMessage(): string
    {
        return $this->shortMessage;
    }
}
