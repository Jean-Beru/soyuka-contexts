<?php

namespace Behatch\HttpCall;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Mink;

/**
 * @method DocumentElement send(string $method, string $url, array $parameters = [], array $files = [], ?string $content = null, array $headers = [])
 * @method string          getMethod()
 * @method string          getUri()
 * @method array           getServer()
 * @method array           getParameters()
 * @method array           getHttpHeaders()
 * @method string          getHttpHeader(string $name)
 * @method void            setHttpHeader(string $name, string $value)
 * @method array           getHttpRawHeader(string $name)
 * @method string          getContent()
 */
class Request
{
    private ?Request\BrowserKit $client = null;

    public function __construct(private readonly Mink $mink)
    {
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->getClient()->{$name}(...$arguments);
    }

    private function getClient(): Request\BrowserKit
    {
        return $this->client ??= new Request\BrowserKit($this->mink);
    }
}
