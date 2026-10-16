<?php

namespace Behatch\HttpCall\Request;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Mink;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

class BrowserKit
{
    public function __construct(protected Mink $mink)
    {
    }

    public function getMethod(): string
    {
        return $this->getRequest()
            ->getMethod();
    }

    public function getUri(): string
    {
        return $this->getRequest()
            ->getUri();
    }

    public function getServer(): array
    {
        return $this->getRequest()
            ->getServer();
    }

    public function getParameters(): array
    {
        return $this->getRequest()
            ->getParameters();
    }

    protected function getRequest(): BrowserKitRequest
    {
        return $this->getClient()->getInternalRequest();
    }

    public function getContent(): string
    {
        return $this->mink->getSession()->getPage()->getContent();
    }

    public function send(string $method, string $url, array $parameters = [], array $files = [], ?string $content = null, array $headers = []): DocumentElement
    {
        $client = $this->getClient();

        foreach ($files as &$file) {
            if (\is_string($file)) {
                // HttpKernelBrowser only keeps UploadedFile instances, HttpBrowser only reads arrays
                $file = $client instanceof HttpKernelBrowser
                    ? new UploadedFile($file, basename($file), null, null, true)
                    : ['tmp_name' => $file, 'name' => basename($file)];
            }
        }

        $client->followRedirects(false);

        // Workaround for https://github.com/symfony/symfony/issues/33393: prevent a default Accept header to be set
        if (!isset($headers['HTTP_ACCEPT']) && '' === $client->getServerParameter('HTTP_ACCEPT')) {
            $headers['HTTP_ACCEPT'] = null;
        }

        $client->request($method, $url, $parameters, $files, $headers, $content);
        $client->followRedirects(true);
        $this->resetHttpHeaders();

        return $this->mink->getSession()->getPage();
    }

    public function setHttpHeader(string $name, string $value): void
    {
        /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */
        $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];
        $name = str_replace('-', '_', strtoupper($name));

        // CONTENT_* are not prefixed with HTTP_ in PHP when building $_SERVER
        if (!isset($contentHeaders[$name])) {
            $name = 'HTTP_'.$name;
        }
        /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */

        $this->getClient()->setServerParameter($name, $value);
    }

    public function getHttpHeaders(): array
    {
        return array_change_key_case(
            $this->mink->getSession()->getResponseHeaders(),
            \CASE_LOWER
        );
    }

    public function getHttpHeader(string $name): string
    {
        $values = $this->getHttpRawHeader($name);

        return implode(', ', $values);
    }

    public function getHttpRawHeader(string $name): array
    {
        $name = strtolower($name);
        $headers = $this->getHttpHeaders();

        if (isset($headers[$name])) {
            $value = $headers[$name];
            if (!\is_array($headers[$name])) {
                $value = [$headers[$name]];
            }
        } else {
            throw new \OutOfBoundsException("The header '$name' doesn't exist");
        }

        return $value;
    }

    protected function resetHttpHeaders(): void
    {
        $this->getClient()->setServerParameters([]);
    }

    private function getClient(): AbstractBrowser
    {
        $driver = $this->mink->getSession()->getDriver();

        if (!$driver instanceof BrowserKitDriver) {
            throw new \LogicException(\sprintf('Sending HTTP requests needs a BrowserKit Mink session such as "browserkit_http", the current session uses "%s".', $driver::class));
        }

        return $driver->getClient();
    }
}
