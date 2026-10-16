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

// URL of the fixtures server (php -S localhost:8080 -t tests/fixtures/www)
$baseUrl = getenv('BEHAT_BASE_URL') ?: 'http://localhost:8080';

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
            'base_url' => $baseUrl,
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
                            'external_base_uri' => $baseUrl,
                        ],
                        // Set BEHAT_CHROMEDRIVER_PORT when the port 9515 is already used
                        'manager_options' => [
                            'port' => (int) (getenv('BEHAT_CHROMEDRIVER_PORT') ?: 9515),
                        ],
                    ],
                ],
            ],
        ]))
        ->withExtension(new Extension(Behatch\Extension::class)))
    // Runs every scenario in the real browser, except those needing response headers,
    // status codes or raw bodies that Panther cannot provide
    ->withProfile((new Profile('panther'))
        ->withSuite((new Suite('default'))
            ->withFilter(new TagFilter('~@user&&~@statusCode&&~@rest&&~@json&&~@xml')))
        ->withExtension(new Extension(MinkExtension::class, [
            'default_session' => 'javascript',
        ])));
