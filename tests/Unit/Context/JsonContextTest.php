<?php

namespace Behatch\Tests\Unit\Context;

use Behat\Gherkin\Node\TableNode;
use Behatch\Context\JsonContext;
use Behatch\HttpCall\HttpCallResult;
use Behatch\HttpCall\HttpCallResultPool;
use PHPUnit\Framework\TestCase;

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

        $this->context = new JsonContext($httpCallResultPool);
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
}
