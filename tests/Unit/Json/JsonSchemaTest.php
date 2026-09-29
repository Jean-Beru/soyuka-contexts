<?php

namespace Behatch\Tests\Unit\Json;

use Behatch\Json\Json;
use Behatch\Json\JsonSchema;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

final class JsonSchemaTest extends TestCase
{
    public function testResolveWithoutUri(): void
    {
        $schema = new JsonSchema('{}');

        $this->assertSame($schema, $schema->resolve(new SchemaStorage()));
    }

    public function testResolveWithUri(): void
    {
        $file = 'file://'.__DIR__.'/../../fixtures/files/schema.json';

        $this->assertEquals((object) ['$id' => $file], (new SchemaStorage())->resolveRef($file));
    }

    public function testValidate(): void
    {
        $this->assertTrue((new JsonSchema('{}'))->validate(new Json('{}'), new Validator()));
    }

    public function testValidateInvalid(): void
    {
        $schema = new JsonSchema('{ "type": "object", "properties": {}, "additionalProperties": false }');

        $this->expectExceptionMessage(<<<EOD
            JSON does not validate. Violations:
              - [] The property foo is not defined and the definition does not allow additional properties

            EOD
        );

        $schema->validate(new Json('{ "foo": { "bar": "foobar" } }'), new Validator());
    }
}
