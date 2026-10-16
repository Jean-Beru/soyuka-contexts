<?php

namespace Behatch\Exception;

use Behat\Testwork\Exception\Stringer\ExceptionStringer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints the short message of a TruncatableExpectationException without -v, the full
 * message with -v and -vv, and the Behat default output with -vvv.
 */
final class TruncatableExceptionStringer implements ExceptionStringer
{
    public function supportsException(\Exception $exception): bool
    {
        return $exception instanceof TruncatableExpectationException;
    }

    public function stringException(\Exception $exception, int $verbosity): string
    {
        \assert($exception instanceof TruncatableExpectationException);

        if (OutputInterface::VERBOSITY_VERBOSE > $verbosity) {
            return \sprintf('%s (run with -v to see the full value) (%s)', $exception->getShortMessage(), $exception::class);
        }

        if (OutputInterface::VERBOSITY_DEBUG > $verbosity) {
            return \sprintf('%s (%s)', $exception->getMessage(), $exception::class);
        }

        return trim((string) $exception);
    }
}
