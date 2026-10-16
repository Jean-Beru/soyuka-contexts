<?php

namespace Behatch\Exception;

use Behat\Testwork\Exception\Stringer\ExceptionStringer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints the short message of a TruncatableExpectationException without -v, and the full
 * message from -v. From -vv, the output is the same as the Behat default output.
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

        if (OutputInterface::VERBOSITY_VERY_VERBOSE > $verbosity) {
            return \sprintf('%s (%s)', $exception->getMessage(), $exception::class);
        }

        if (OutputInterface::VERBOSITY_DEBUG > $verbosity) {
            return $this->stringWithoutBehatCalls($exception);
        }

        return trim((string) $exception);
    }

    /**
     * Same output as Behat\Testwork\Exception\ExceptionPresenter for -vv.
     */
    private function stringWithoutBehatCalls(\Exception $exception): string
    {
        $traceOutput = '';
        foreach ($exception->getTrace() as $i => $trace) {
            if (isset($trace['file']) && str_contains(str_replace('\\', '/', $trace['file']), 'Behat/Testwork/Call/Handler/RuntimeCallHandler')) {
                break;
            }

            $traceOutput .= \sprintf(
                '#%d %s: %s()'.\PHP_EOL,
                $i,
                isset($trace['file']) ? $trace['file'].'('.($trace['line'] ?? 0).')' : '[internal function]',
                isset($trace['class']) ? $trace['class'].($trace['type'] ?? '::').$trace['function'] : $trace['function']
            );
        }

        return \sprintf(
            '%s: %s in %s:%d%sStack trace:%s%s',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            \PHP_EOL,
            \PHP_EOL,
            $traceOutput
        );
    }
}
