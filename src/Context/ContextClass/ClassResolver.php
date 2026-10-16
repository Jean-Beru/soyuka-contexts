<?php

namespace Behatch\Context\ContextClass;

use Behat\Behat\Context\ContextClass\ClassResolver as BaseClassResolver;

class ClassResolver implements BaseClassResolver
{
    public function supportsClass(string $contextClass): bool
    {
        return str_starts_with($contextClass, 'behatch:context:');
    }

    public function resolveClass(string $contextClass): string
    {
        return str_replace(':', '\\', ucwords($contextClass, ':')).'Context';
    }
}
