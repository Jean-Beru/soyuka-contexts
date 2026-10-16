UPGRADE FROM 3.4 to 4.0
=======================

Upgrade path
------------

Version 3.4 supports Behat 3.20+ and Behat 4, PHP attributes and the `behat.php`
configuration. It is backward compatible with the previous 3.x versions. Upgrade in
three steps, and run your suite after each step:

1. Upgrade to 3.4: `composer require --dev soyuka/contexts:^3.4`.
2. Upgrade to Behat 4 with version 3.4: follow the "Behat 4" section below, then run
   `composer require --dev -W behat/behat:^4.0 friends-of-behat/mink-extension:^3.0@alpha`.
3. Upgrade to 4.0: follow the other sections below.

Requirements
------------

* PHP 8.3 or higher is required (3.4 supports PHP 8.1+).
* Behat 4 is required. Behat 3 is not supported anymore.
* Symfony 7.4 or 8 is required.
* `friends-of-behat/mink-extension` 3 is required. It is only available as an alpha
  release for now, require it from your root `composer.json` along with this library:
  `composer require --dev -W soyuka/contexts:^4.0 friends-of-behat/mink-extension:^3.0@alpha`.
  If you use the JSON steps, add `symfony/property-access` and `justinrainbow/json-schema`
  to this command. Otherwise `-W` removes them (see "Dependencies").

Behat 4
-------

Skip this section if you already use Behat 4 with version 3.4.

* Behat 4 does not read `behat.yml` anymore: move your configuration to `behat.php`.
  Run `vendor/bin/behat --convert-config` before upgrading, while Behat 3.21+ is still
  installed (the command does not exist in Behat 4). The generated file imports
  `Behatch\Extension`, which then shadows `Behat\Config\Extension`: replace
  `use Behatch\Extension;` with `use Behat\Config\Extension;` and register the extension
  with `new Extension(Behatch\Extension::class)`.
* Extensions must be registered by their class name: replace `Behat\MinkExtension` with
  `Behat\MinkExtension\ServiceContainer\MinkExtension::class`.
* Steps, hooks and transformations of your own contexts extending the Behatch ones must
  use PHP attributes (`#[Behat\Step\Then('...')]`) instead of docblock annotations.

Drivers and sessions
--------------------

* Goutte support is removed: use the `browserkit_http` Mink session
  (`behat/mink-browserkit-driver`, `symfony/http-client` and `symfony/mime`).
* This project is not tested against Selenium2 anymore. Behatch still handles the
  Selenium2 driver exceptions. Use any Mink driver supported by the Mink extension 3 (e.g.
  `selenium4`, `webdriver_classic` or Panther).
* `Behatch\HttpCall\Request\Goutte` is removed and the `symfony2` Mink session name has no
  special meaning anymore: headers added with `I add :name header equal to :value` are
  stored as client server parameters, which are reset after each request.
* With a kernel browser session, uploaded files are sent with their file name instead of
  the request parameter name. Version 3.4 already did this with `browserkit_http`.
* The steps that send requests or add request headers need a BrowserKit Mink session
  (`browserkit_http`, or a kernel browser session): they throw a `LogicException` with an
  explicit message on other drivers, Panther included.
* The Behatch extension throws a `LogicException` when the Mink extension is not
  registered.

Dependencies
------------

The following packages are no longer installed with this library. `composer require -W`
removes them when no other package requires them. Install the ones you need with
`composer require --dev`:

* `symfony/property-access` for the JSON node steps;
* `justinrainbow/json-schema` for the JSON schema validation steps;
* `symfony/dom-crawler` and `symfony/http-foundation` are not required anymore
  (`symfony/http-foundation` is only used with kernel browser sessions, which already
  install it).

Steps using a missing package throw a `Behatch\MissingDependencyException`. When installed,
`justinrainbow/json-schema` 5.3+ and `symfony/property-access` 6.4+ are required
(enforced by `conflict`).

Removed steps and methods
-------------------------

* `Given I create the file :filename contening:` is removed, use
  `Given I create the file :filename containing:`.
* `RestContext::theHeaderShouldBeContains()` is removed, use
  `RestContext::theHeaderShouldContain()`.

Extension points
----------------

Version 3.4 already declares the return types required by Behat 4 on
`Extension::getConfigKey()`, `ClassResolver`, `HttpCallResultPoolResolver::resolveArguments()`,
`HttpCallListener::getSubscribedEvents()` and `BaseContext::getTranslationResources()`.
Update the classes that extend or implement them:

* `Behatch\Extension::getCompilerPasses()` is removed;
* `Behatch\Context\ContextClass\ClassResolver::supportsClass()` and `resolveClass()` take a
  `string`;
* `SystemContext::getTranslationResources()` returns an `array`;
* classes that use the `Behatch\Html` trait must declare
  `getSession(?string $name = null): Session`, as `RawMinkContext` does;
* `Asserter::not()`, `assert()`, `assertContains()` and `assertNotContains()` take an
  optional `?string $shortMessage` last argument. Add it to the methods that you override.

Native types
------------

Every property, argument and return value of the library now has a native type. Overriding
methods may keep untyped arguments, but must declare the new return types, and redeclared
properties must use the same type:

* custom context voters: `ContextSupportedVoter::vote(): bool` and
  `FilterableHttpCallResult::filter(): mixed`;
* steps returning a value: `RestContext::iSendARequestTo()`,
  `iSendARequestToWithParameters()` and `iSendARequestToWithBody()` return a
  `DocumentElement`, `RestContext::theHeaderShouldExist()` and
  `XmlContext::theXmlAttributeShouldExist()` a `string`,
  `XmlContext::theXmlElementShouldExist()` a `\DOMNodeList` and
  `JsonContext::theJsonNodeShouldExist()` is `mixed`;
* `BrowserContext::iAmOnUrlComposedBy()`, `iFillInWithTheCurrentDate()` and
  `iFillInWithTheCurrentDateAndModifier()` return `void` (they always returned `null`);
* helpers: `BaseContext::castToInt(): int|string`, `BaseContext::getMinkContext(): MinkContext`,
  `JsonContext::getJson(): Json`, `JsonContext::reespaceSpecialGherkinValue(string)`,
  `Html::countElements(): int`,
  `Html::findElement(): NodeElement`, `HttpCallListener::afterStep(): void`, and every method
  of `Json`, `JsonSchema`, `JsonInspector`, `Xml\Dom`, `HttpCallResult`,
  `HttpCallResultPool`, `Request` and `Request\BrowserKit`;
* protected properties: `RestContext::$request`, `JsonContext::$inspector`,
  `JsonContext::$httpCallResultPool`, `Json::$content` and `Request\BrowserKit::$mink`;
* `Asserter` methods take typed arguments: `assertContains()` and `assertNotContains()`
  take strings, `assertCount()` an `int` and an `array`, `assertArrayHasKey()` and
  `assertArrayNotHasKey()` an `int|string` key and an `array|\ArrayAccess`, and messages
  are `?string`. Cast the values before you pass them;
* `Asserter::assert()`, `assertTrue()` and `assertFalse()` take a `bool`: `assertTrue()` now
  fails on falsy values such as `0` or `''` (only `false` failed before) and `null` is a
  `TypeError`. Assertion messages are strings;
* context parameters: `BrowserContext` `timeout` is a `float`, `DebugContext`
  `screenshotDir`, `SystemContext` `root` and `JsonContext` `evaluationMode` are strings.

Behavior fixes
--------------

* `I execute :command` resets the output of the previous command instead of appending to it.
* Numeric step arguments turned into integers by `BaseContext::castToInt()` are compared as
  strings again by `the JSON node :node should be equal to the string :expected` and
  `the XML attribute ... should not be equal to :text`.
* `the XML attribute :attribute on element :element should exist` accepts the `"0"` value.
* `the JSON node :node should match :pattern` fails on an invalid pattern.
* `the response should not be in XML` passes on an empty response.
* `Json::encode()` throws a `\JsonException` when it cannot encode the content, instead of
  returning `false`.
* `Xml\Dom` throws a `\DOMException` instead of a `\ValueError` on empty content.
* `I put a breakpoint` reads one line, and does not loop forever when the input is closed.
* Without `-v`, the failure message of `the JSON node :node should (not) contain :text`
  shows only the first 200 characters of a long node value. Run Behat with `-v` to see
  the full value.
* Negated steps (`... should not ...`) fail instead of passing when the driver cannot answer
  them, e.g. `the :element element should not be visible` on a session without JavaScript
  or `the header :name should not exist` on a browser session. Run these steps with a driver
  that supports them: a JavaScript driver for visibility, a BrowserKit session for headers.

Known issue
-----------

With `friends-of-behat/mink-extension` v3.0.0-ALPHA.1, translated `MinkContext` steps (for
instance `je devrais voir "..."`) are undefined because their patterns do not match the
translation files. It is fixed upstream (FriendsOfBehat/MinkExtension#65) and will work
with the next mink-extension release (Panther users also need a
`robertfausk/behat-panther-extension` release that allows it).
