<?php

namespace Behatch\Tests\Unit\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behatch\Context\JsonContext;
use Behatch\HttpCall\HttpCallResult;
use Behatch\HttpCall\HttpCallResultPool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;

final class JsonContextTest extends TestCase
{
    private JsonContext $context;

    protected function setUp(): void
    {
        $httpCallResultPool = new HttpCallResultPool();
        $httpCallResultPool->store(
            new HttpCallResult(json_encode([
                'a string node' => 'some string',
                'another string node' => 'some other string',
                'a null node' => null,
                'a true node' => true,
                'a false node' => false,
                'a number node' => 3,
                'a numeric string node' => '12',
                'an array node' => [
                    'one',
                    'two',
                    'three',
                ],
            ], \JSON_THROW_ON_ERROR))
        );

        $this->context = $this->createContext($httpCallResultPool);
    }

    private function createContext(HttpCallResultPool $httpCallResultPool): JsonContext
    {
        $mink = new Mink(['default' => new Session(new BrowserKitDriver(new HttpBrowser()))]);
        $mink->setDefaultSessionName('default');

        $context = new JsonContext($httpCallResultPool);
        $context->setMink($mink);

        return $context;
    }

    public function testTheJsonNodeShouldContain(): void
    {
        $this->context->theJsonNodeShouldContain('a string node', 'string');
        $this->context->theJsonNodeShouldContain('a number node', '3');

        $exception = $this->catchExpectation(fn () => $this->context->theJsonNodeShouldContain('a string node', 'expected'));

        $this->assertSame(ExpectationException::class, $exception::class);
        $this->assertSame("The node 'a string node' should contain 'expected', but it is 'some string'.", $exception->getMessage());
    }

    public function testTheJsonNodesShouldContain(): void
    {
        $this->context->theJsonNodesShouldContain(new TableNode([
            1 => ['a string node', 'some'],
            2 => ['another string node', 'other'],
        ]));

        $this->expectExceptionMessage("The node 'another string node' should contain 'expected', but it is 'some other string'.");
        $this->context->theJsonNodesShouldContain(new TableNode([
            1 => ['a string node', 'some'],
            2 => ['another string node', 'expected'],
        ]));
    }

    public function testTheJsonNodeShouldNotContain(): void
    {
        $this->context->theJsonNodeShouldNotContain('a string node', 'expected');

        $exception = $this->catchExpectation(fn () => $this->context->theJsonNodeShouldNotContain('a number node', '3'));

        $this->assertSame(ExpectationException::class, $exception::class);
        $this->assertSame("The node 'a number node' should not contain '3', but it is '3'.", $exception->getMessage());
    }

    public function testTheJsonNodesShouldNotContain(): void
    {
        $this->context->theJsonNodesShouldNotContain(new TableNode([
            1 => ['a string node', 'other'],
            2 => ['another string node', 'expected'],
        ]));

        $this->expectExceptionMessage("The node 'another string node' should not contain 'other', but it is 'some other string'.");
        $this->context->theJsonNodesShouldNotContain(new TableNode([
            1 => ['a string node', 'other'],
            2 => ['another string node', 'other'],
        ]));
    }

    private function catchExpectation(callable $step): ExpectationException
    {
        try {
            $step();
        } catch (ExpectationException $exception) {
            return $exception;
        }

        $this->fail('The step did not fail.');
    }

    public function testTheJsonNodeShouldBeEqualTo(): void
    {
        $this->context->theJsonNodeShouldBeEqualTo('a string node', 'some string');

        $this->expectExceptionMessage("The node 'a string node' value is '\"some string\"', 'expectedstring' expected");
        $this->context->theJsonNodeShouldBeEqualTo('a string node', 'expectedstring');
    }

    public function testTheJsonNodesShouldBeEqualTo(): void
    {
        $this->context->theJsonNodesShouldBeEqualTo(new TableNode([
            1 => ['a string node', 'some string'],
            2 => ['another string node', 'some other string'],
        ]));

        $this->expectExceptionMessage("The node 'a string node' value is '\"some string\"', 'may the force' expected\nThe node 'another string node' value is '\"some other string\"', 'be with you' expected");
        $this->context->theJsonNodesShouldBeEqualTo(new TableNode([
            1 => ['a string node', 'may the force'],
            2 => ['another string node', 'be with you'],
        ]));
    }

    public function testTheJsonNodeShouldMatch(): void
    {
        $this->context->theJsonNodeShouldMatch('a string node', '/some/');

        $this->expectExceptionMessage("The node 'a string node' value is '\"some string\"', '/nomatch/' pattern expected");
        $this->context->theJsonNodeShouldMatch('a string node', '/nomatch/');
    }

    public function testTheJsonNodeShouldBeNull(): void
    {
        $this->context->theJsonNodeShouldBeNull('a null node');

        $this->expectExceptionMessage("The node 'a string node' value is '\"some string\"', null expected");
        $this->context->theJsonNodeShouldBeNull('a string node');
    }

    public function testTheJsonNodeShouldNotBeNull(): void
    {
        $this->context->theJsonNodeShouldNotBeNull('a string node');

        $this->expectExceptionMessage("The node 'a null node' value is null, non-null value expected");
        $this->context->theJsonNodeShouldNotBeNull('a null node');
    }

    public function testTheJsonNodeShouldBeTrue(): void
    {
        $this->context->theJsonNodeShouldBeTrue('a true node');

        $this->expectExceptionMessage("The node 'a false node' value is 'false', 'true' expected");
        $this->context->theJsonNodeShouldBeTrue('a false node');
    }

    public function testTheJsonNodeShouldBeFalse(): void
    {
        $this->context->theJsonNodeShouldBeFalse('a false node');

        $this->expectExceptionMessage("The node 'a true node' value is 'true', 'false' expected");
        $this->context->theJsonNodeShouldBeFalse('a true node');
    }

    public function testTheJsonNodeShouldBeEqualToTheString(): void
    {
        $this->context->theJsonNodeShouldBeEqualToTheString('a string node', 'some string');

        $this->expectExceptionMessage("The node 'a string node' value is '\"some string\"', string 'expected' expected");
        $this->context->theJsonNodeShouldBeEqualToTheString('a string node', 'expected');
    }

    public function testTheJsonNodeShouldBeEqualToANumericString(): void
    {
        // Behat turns the "12" step argument into an int with the castToInt() transformation
        $this->context->theJsonNodeShouldBeEqualToTheString('a numeric string node', $this->context->castToInt('12'));

        $this->expectExceptionMessage("The node 'a number node' value is '3', string '3' expected");
        $this->context->theJsonNodeShouldBeEqualToTheString('a number node', '3');
    }

    public function testTheJsonNodeShouldBeEqualToTheNumber(): void
    {
        $this->context->theJsonNodeShouldBeEqualToTheNumber('a number node', 3);

        $this->expectExceptionMessage("The node 'a number node' value is '3', number '2' expected");
        $this->context->theJsonNodeShouldBeEqualToTheNumber('a number node', 2);
    }

    public function testTheJsonNodeShouldExist(): void
    {
        $this->context->theJsonNodeShouldExist('a string node');

        $this->expectExceptionMessage("The node 'invalid key' does not exist.");
        $this->context->theJsonNodeShouldExist('invalid key');
    }

    public function testTheJsonNodeShouldHaveElements(): void
    {
        $this->context->theJsonNodeShouldHaveElements('an array node', 3);

        $this->assertSame("The node 'an array node' contains 3 element(s), 2 expected.", $this->catchExpectation(fn () => $this->context->theJsonNodeShouldHaveElements('an array node', 2))->getMessage());
        $this->assertSame("The node 'an array node' contains 3 element(s), 5 expected.", $this->catchExpectation(fn () => $this->context->theJsonNodeShouldHaveElements('an array node', 5))->getMessage());
    }
}
