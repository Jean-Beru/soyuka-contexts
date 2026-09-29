<?php

namespace Behatch\Tests\Unit\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\SystemContext;
use PHPUnit\Framework\TestCase;

final class SystemContextTest extends TestCase
{
    private SystemContext $context;

    protected function setUp(): void
    {
        $this->context = new SystemContext();
    }

    public function testCommandShouldSucceed(): void
    {
        $this->context->iExecute('true');
        $this->context->commandShouldSucceed();

        $this->context->iExecute('exit 2');
        $this->expectExceptionMessage('Command should succeed 2');
        $this->context->commandShouldSucceed();
    }

    public function testCommandShouldFail(): void
    {
        $this->context->iExecute('exit 2');
        $this->context->commandShouldFail();

        $this->context->iExecute('true');
        $this->expectExceptionMessage('Command should fail 0');
        $this->context->commandShouldFail();
    }

    public function testOutputShouldBe(): void
    {
        $this->context->iExecute("printf 'Hello world\\nHow are you?\\n'");
        $this->context->outputShouldBe(new PyStringNode(['Hello world', 'How are you?'], 1));
        $this->context->outputShouldBe(new PyStringNode(['Hello world', 'How are you?', 'Fine'], 1));

        foreach ([['Hello John', 'How are you?'], ['Hello world']] as $lines) {
            $this->assertSame("instead of\nHello world\nHow are you?", $this->catchException(fn () => $this->context->outputShouldBe(new PyStringNode($lines, 1)))->getMessage());
        }
    }

    public function testOutputShouldNotBe(): void
    {
        $this->context->iExecute("printf 'Hello world\\nHow are you?\\n'");
        $this->context->outputShouldNotBe(new PyStringNode(['Hello John', 'How are you?'], 1));
        $this->context->outputShouldNotBe(new PyStringNode(['Hello world'], 1));

        foreach ([['Hello world', 'How are you?'], ['Hello world', 'How are you?', 'Fine']] as $lines) {
            $this->assertSame('Output should not be', $this->catchException(fn () => $this->context->outputShouldNotBe(new PyStringNode($lines, 1)))->getMessage());
        }
    }

    private function catchException(callable $step): \Exception
    {
        try {
            $step();
        } catch (\Exception $exception) {
            return $exception;
        }

        $this->fail('The step did not fail.');
    }
}
