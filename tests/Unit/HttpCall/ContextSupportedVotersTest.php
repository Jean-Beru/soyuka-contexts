<?php

namespace Behatch\Tests\Unit\HttpCall;

use Behatch\HttpCall\ContextSupportedVoters;
use Behatch\HttpCall\HttpCallResult;
use PHPUnit\Framework\TestCase;

final class ContextSupportedVotersTest extends TestCase
{
    public function testVoteWithoutVoters(): void
    {
        $this->assertFalse((new ContextSupportedVoters())->vote(new HttpCallResult('')));
    }
}
