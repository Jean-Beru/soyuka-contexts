<?php

namespace Behatch\Tests\Unit\Json;

use Behatch\Json\Json;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class JsonTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertSame(['foo' => 'bar'], (array) (new Json('{"foo": "bar"}'))->getContent());
    }

    public function testConstructInvalidJson(): void
    {
        $this->expectExceptionMessage("The string '{{json' is not valid json");

        new Json('{{json');
    }

    public function testToString(): void
    {
        $content = '{"foo":"bar"}';

        $this->assertSame($content, (string) new Json($content));
    }

    public function testRead(): void
    {
        $json = new Json('{"foo":"bar"}');

        $this->assertSame('bar', $json->read('foo', PropertyAccess::createPropertyAccessor()));
    }

    public function testReadInvalidExpression(): void
    {
        $json = new Json('{"foo":"bar"}');

        $this->expectException(NoSuchPropertyException::class);

        $json->read('jeanmarc', PropertyAccess::createPropertyAccessor());
    }
}
