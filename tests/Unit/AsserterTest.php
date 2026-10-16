<?php

namespace Behatch\Tests\Unit;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behatch\Context\RestContext;
use Behatch\HttpCall\Request;
use PHPUnit\Framework\TestCase;

final class AsserterTest extends TestCase
{
    public function testNegatedStepFailsWhenTheDriverCannotAnswer(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getResponseHeaders')->willThrowException(new UnsupportedDriverActionException('%s does not support response headers', $driver));

        $mink = new Mink(['default' => new Session($driver)]);
        $mink->setDefaultSessionName('default');

        $this->expectException(UnsupportedDriverActionException::class);

        (new RestContext(new Request($mink)))->theHeaderShouldNotExist('X-Foo');
    }

    public function testNegatedStepPassesOnOtherLogicExceptions(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getResponseHeaders')->willThrowException(new \InvalidArgumentException());

        $mink = new Mink(['default' => new Session($driver)]);
        $mink->setDefaultSessionName('default');

        $this->expectNotToPerformAssertions();

        (new RestContext(new Request($mink)))->theHeaderShouldNotExist('X-Foo');
    }
}
