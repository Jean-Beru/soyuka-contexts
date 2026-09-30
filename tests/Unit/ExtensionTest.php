<?php

namespace Behatch\Tests\Unit;

use Behatch\Extension;
use Behatch\HttpCall\ContextSupportedVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ExtensionTest extends TestCase
{
    public function testMinkExtensionIsRequired(): void
    {
        $container = new ContainerBuilder();
        $extension = new Extension();
        $extension->load($container, []);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('needs the Mink extension');

        $extension->process($container);
    }

    public function testVotersAreRegisteredByPriority(): void
    {
        $container = new ContainerBuilder();
        $extension = new Extension();
        $extension->load($container, []);
        // registered after Behatch was loaded, as another extension would
        $container->register('mink', \stdClass::class);
        $container->register('late.voter', ContextSupportedVoter::class)->addTag('behatch.context_voter', ['priority' => 10]);

        $extension->process($container);

        $this->assertEquals([
            ['register', [new Reference('late.voter')]],
            ['register', [new Reference('behatch.rest_context_supported.voter')]],
        ], $container->getDefinition('behatch.context_supported.voter')->getMethodCalls());
    }
}
