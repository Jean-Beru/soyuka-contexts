<?php

namespace Behatch\Tests\Unit\Context;

use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use PHPUnit\Framework\TestCase;

final class RestContextTest extends TestCase
{
    public function testPrintTheCorrespondingCurlCommand(): void
    {
        // Server parameters of a BrowserKit request after "I add "X-Foo-Bar" header equal to "baz""
        $request = $this->createStub(Request::class);
        $request->method('__call')->willReturnCallback(static fn (string $name): mixed => match ($name) {
            'getMethod' => 'POST',
            'getUri' => 'http://localhost:8080/rest/index.php',
            'getParameters' => ['foo' => 'bar'],
            'getServer' => [
                'HTTP_USER_AGENT' => 'Symfony BrowserKit',
                'HTTP_X_FOO_BAR' => 'baz',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'HTTP_ACCEPT' => null,
                'HTTP_HOST' => 'localhost:8080',
                'HTTPS' => false,
            ],
            default => throw new \BadMethodCallException($name),
        });

        ob_start();
        (new RestContext($request))->printTheCorrespondingCurlCommand();

        $this->assertSame(
            "curl -X POST --data 'foo=bar' -H 'User-Agent: Symfony BrowserKit' -H 'X-Foo-Bar: baz' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Host: localhost:8080' 'http://localhost:8080/rest/index.php'",
            ob_get_clean()
        );
    }
}
