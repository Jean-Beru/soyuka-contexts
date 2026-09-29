<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behatch\Tests\Behat\MinkTranslationContext;
use Robertfausk\Behat\PantherExtension\ServiceContainer\PantherExtension;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('default'))
            ->withPaths('%paths.base%/tests/features')
            ->withContexts(MinkContext::class, MinkTranslationContext::class)
            ->addContext('behatch:context:browser', ['timeout' => 1])
            ->addContext('behatch:context:debug', ['screenshotDir' => '.'])
            ->addContext('behatch:context:json', ['evaluationMode' => 'javascript'])
            ->addContext('behatch:context:rest')
            ->addContext('behatch:context:system', ['root' => '.'])
            ->addContext('behatch:context:table')
            ->addContext('behatch:context:xml')
            ->withFilter(new TagFilter('~@user')))
        ->withExtension(new Extension(PantherExtension::class))
        ->withExtension(new Extension(MinkExtension::class, [
            'base_url' => 'http://localhost:8080',
            'files_path' => '%paths.base%/tests/fixtures/files',
            'default_session' => 'default',
            'javascript_session' => 'javascript',
            'sessions' => [
                'default' => [
                    'browserkit_http' => [],
                ],
                'javascript' => [
                    'panther' => [
                        'options' => [
                            'browser' => 'chrome',
                            'external_base_uri' => 'http://localhost:8080',
                        ],
                    ],
                ],
            ],
        ]))
        ->withExtension(new Extension(Behatch\Extension::class)));
