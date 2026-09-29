<?php

namespace Behatch\Tests\Unit\Json;

use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Behatch\Json\JsonSchema;
use PHPUnit\Framework\TestCase;

final class JsonInspectorTest extends TestCase
{
    public function testEvaluate(): void
    {
        $json = new Json('{ "foo": { "bar": "foobar" } }');

        $this->assertSame('foobar', (new JsonInspector('php'))->evaluate($json, 'foo.bar'));
    }

    public function testEvaluateInvalid(): void
    {
        $this->expectExceptionMessage("Failed to evaluate expression 'foo.bar'");

        (new JsonInspector('php'))->evaluate(new Json('{}'), 'foo.bar');
    }

    public function testEvaluateJavascriptMode(): void
    {
        $json = new Json('{ "foo": { "bar": "foobar" } }');

        $this->assertSame('foobar', (new JsonInspector('javascript'))->evaluate($json, 'foo->bar'));
    }

    public function testValidate(): void
    {
        $json = new Json('{ "foo": { "bar": "foobar" } }');

        $this->assertTrue((new JsonInspector('php'))->validate($json, new JsonSchema('{}')));
    }
}
