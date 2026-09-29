<?php

namespace Behatch\HttpCall;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Mink;

/**
 * @method DocumentElement send(string $method, string $url, array $parameters = [], array $files = [], string $content = null, array $headers = [])
 * @method string          getMethod()
 * @method string          getUri()
 * @method array           getServer()
 * @method array           getParameters()
 * @method array           getHttpHeaders()
 * @method string          getHttpHeader(string $name)
 * @method void            setHttpHeader(string $name, string $value)
 * @method string          getHttpRawHeader(string $name)
 * @method string          getContent()
 */
class Request
{
    /**
     * @var Mink
     */
    private $mink;
    private $client;

    /**
     * Request constructor.
     */
    public function __construct(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @param string $name
     */
    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->getClient(), $name], $arguments);
    }

    /**
     * @return Request\BrowserKit
     */
    private function getClient()
    {
        return $this->client ??= new Request\BrowserKit($this->mink);
    }
}
