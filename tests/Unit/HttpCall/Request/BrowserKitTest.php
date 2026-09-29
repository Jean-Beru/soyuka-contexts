<?php

namespace Behatch\Tests\Unit\HttpCall\Request;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behatch\HttpCall\Request\BrowserKit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class BrowserKitTest extends TestCase
{
    public function testUploadWithHttpKernelBrowser(): void
    {
        $kernel = new class implements HttpKernelInterface {
            public ?Request $request = null;

            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                $this->request = $request;

                return new Response();
            }
        };

        $this->createBrowserKit(new BrowserKitDriver(new HttpKernelBrowser($kernel)))
            ->send('POST', 'http://localhost/', [], ['upload' => __DIR__.'/../../../fixtures/files/lorem.txt']);

        $file = $kernel->request->files->get('upload');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('lorem.txt', $file->getClientOriginalName());
    }

    public function testRequestsNeedABrowserKitSession(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('needs a BrowserKit Mink session');

        $this->createBrowserKit($this->createStub(DriverInterface::class))->send('GET', 'http://localhost/');
    }

    private function createBrowserKit(DriverInterface $driver): BrowserKit
    {
        $mink = new Mink(['default' => new Session($driver)]);
        $mink->setDefaultSessionName('default');

        return new BrowserKit($mink);
    }
}
