<?php

namespace Behatch\Tests\Unit;

use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\JsonContext;
use Behatch\HttpCall\HttpCallResult;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Behatch\Json\JsonSchema;
use Behatch\MissingDependencyException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Excluded by default: run with `--group missing-dependencies` once the optional packages
 * are removed (see the CI "without optional dependencies" job).
 */
#[Group('missing-dependencies')]
final class MissingDependencyTest extends TestCase
{
    public function testJsonNodeAccessRequiresPropertyAccess(): void
    {
        $this->skipIfInstalled(PropertyAccessor::class);

        $this->expectException(MissingDependencyException::class);
        $this->expectExceptionMessage('composer require --dev symfony/property-access');

        (new JsonInspector('php'))->evaluate(new Json('{}'), 'foo');
    }

    public function testNegatedNodeStepDoesNotHideMissingPropertyAccess(): void
    {
        $this->skipIfInstalled(PropertyAccessor::class);

        $this->expectException(MissingDependencyException::class);

        $this->createJsonContext()->theJsonNodeShouldNotExist('foo');
    }

    public function testSchemaValidationRequiresJsonSchema(): void
    {
        $this->skipIfInstalled(\JsonSchema\Validator::class);

        $this->expectException(MissingDependencyException::class);
        $this->expectExceptionMessage('composer require --dev justinrainbow/json-schema');

        (new JsonInspector('php'))->validate(new Json('{}'), new JsonSchema('{}'));
    }

    public function testNegatedSchemaStepDoesNotHideMissingJsonSchema(): void
    {
        $this->skipIfInstalled(\JsonSchema\Validator::class);

        $this->expectException(MissingDependencyException::class);

        $this->createJsonContext()->theJsonShouldBeInvalidAccordingToThisSchema(new PyStringNode(['{}'], 1));
    }

    private function skipIfInstalled(string $class): void
    {
        if (class_exists($class)) {
            $this->markTestSkipped(\sprintf('%s is installed.', $class));
        }
    }

    private function createJsonContext(): JsonContext
    {
        $httpCallResultPool = new HttpCallResultPool();
        $httpCallResultPool->store(new HttpCallResult('{}'));

        return new JsonContext($httpCallResultPool);
    }
}
