<?php

namespace Behatch;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behatch\Context\ContextClass\ClassResolver;
use Behatch\Exception\TruncatableExceptionStringer;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension implements ExtensionInterface
{
    use PriorityTaggedServiceTrait;

    public function getConfigKey(): string
    {
        return 'behatch';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('mink')) {
            throw new \LogicException(\sprintf('The "%s" extension needs the Mink extension: register "new Extension(%s::class, [...])" in your behat.php.', self::class, 'Behat\MinkExtension\ServiceContainer\MinkExtension'));
        }

        $this->loadHttpCallListener($container);
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources/services'));
        $loader->load('http_call.yml');

        $this->loadClassResolver($container);
        $this->loadExceptionStringer($container);
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    private function loadClassResolver(ContainerBuilder $container): void
    {
        $definition = new Definition(ClassResolver::class);
        $definition->addTag(ContextExtension::CLASS_RESOLVER_TAG);
        $container->setDefinition('behatch.class_resolver', $definition);
    }

    private function loadExceptionStringer(ContainerBuilder $container): void
    {
        $definition = new Definition(TruncatableExceptionStringer::class);
        $definition->addTag(ExceptionExtension::STRINGER_TAG);
        $container->setDefinition('behatch.exception_stringer.truncatable', $definition);
    }

    private function loadHttpCallListener(ContainerBuilder $container): void
    {
        $references = $this->findAndSortTaggedServices('behatch.context_voter', $container);
        $definition = $container->getDefinition('behatch.context_supported.voter');

        foreach ($references as $reference) {
            $definition->addMethodCall('register', [$reference]);
        }
    }
}
