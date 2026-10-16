Behatch contexts
================

[![CI](https://github.com/soyuka/contexts/actions/workflows/ci.yml/badge.svg)](https://github.com/soyuka/contexts/actions/workflows/ci.yml)

Behatch contexts provide most common Behat tests.

Installation
------------

This extension requires:

* PHP 8.3+
* Behat 4
* Symfony 7.4 or 8
* [Mink extension](https://github.com/FriendsOfBehat/MinkExtension) 3

With Behat 3, use version 3.4, which supports Behat 3.20+ and Behat 4. To upgrade from
3.x, see [UPGRADE-4.0.md](./UPGRADE-4.0.md).

### Project dependency

1. [Install Composer](https://getcomposer.org/download/)
2. Require the package with Composer:

```
$ composer require --dev soyuka/contexts friends-of-behat/mink-extension:^3.0@alpha \
    behat/mink-browserkit-driver symfony/http-client symfony/mime symfony/property-access
```

`friends-of-behat/mink-extension` 3 is only available as an alpha release for now.
Composer only reads stability flags from the root `composer.json`, so it must be
required explicitly as shown above.
With mink-extension v3.0.0-ALPHA.1, the translated `MinkContext` steps are undefined. See
the known issue in [UPGRADE-4.0.md](./UPGRADE-4.0.md#known-issue).

3. Activate the Mink and Behatch extensions in your `behat.php` (extensions must be
   registered by their class name):

```php
// behat.php
use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\ServiceContainer\MinkExtension;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('default'))
            ->withContexts(MinkContext::class, 'behatch:context:rest', 'behatch:context:json'))
        ->withExtension(new Extension(MinkExtension::class, [
            'base_url' => 'http://localhost:8000',
            'sessions' => ['default' => ['browserkit_http' => []]],
        ]))
        ->withExtension(new Extension(Behatch\Extension::class)));
```

The Mink extension is always required. See [Mink
extension](https://github.com/FriendsOfBehat/MinkExtension) for other sessions and
drivers.

To test a Symfony application through its kernel, without a web server, use the `symfony`
Mink session of [friends-of-behat/symfony-extension](https://github.com/FriendsOfBehat/SymfonyExtension)
(version 2.7+ supports Behat 4). The request steps also work with this session.

### Optional packages

Some steps need packages that are not installed by default. The JSON steps throw
an exception telling which package to install when they are used without it:

* `behat/mink-browserkit-driver`, `symfony/http-client` and `symfony/mime` - to send
  HTTP requests without a browser (`browserkit_http` Mink session)
* `symfony/property-access` - JSON node steps (`json` context)
* `justinrainbow/json-schema` - JSON schema validation steps (`json` context)
* A JavaScript capable Mink driver (e.g. `robertfausk/behat-panther-extension`, which also
  needs `phpunit/phpunit`) - steps
  that need a real browser (`browser` and `debug` contexts)

Usage
-----

In `behat.php`, enable desired contexts:

```php
use Behat\Config\Suite;

(new Suite('default'))
    ->withContexts(
        'behatch:context:browser',
        'behatch:context:debug',
        'behatch:context:system',
        'behatch:context:json',
        'behatch:context:table',
        'behatch:context:rest',
        'behatch:context:xml',
    );
```

### Examples

This project is self-tested, you can explore the [features
directory](./tests/features) to find some examples and [behat.dist.php](./behat.dist.php)
for a complete configuration.

Configuration
-------------

* `browser` - more browser related steps (like mink)
    * `timeout` - default timeout
* `debug` - helper steps for debugging
    * `screenshotDir` - the directory where store screenshots
* `system` - shell related steps
    * `root` - the root directory of the filesystem
* `json` - JSON related steps
    * `evaluationMode` - javascript "foo.bar" or php "foo->bar"
* `table` - play with HTML the tables
* `rest` - send GET, POST, ... requests and test the HTTP headers
* `xml` - XML related steps

### Configuration Example

For example, if you want to change default directory to screenshots - you can do it this way:

```php
(new Suite('default'))
    ->addContext('behatch:context:debug', ['screenshotDir' => 'var']);
```
