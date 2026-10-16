# Contributing

Behatch is an open source, community-driven project. If you'd like to
contribute, feel free to do this, but remember to follow this few simple rules:

* Make your feature addition or bug fix,
* __Always__ as base for your changes use `main` branch (all new development
  happens here),
* Add `*.features` for those changes (please look into `tests/features/` folder for
  some examples). This is important so we don't break it in a future version
  unintentionally,
* __Remember__: when you create Pull Request, always select `main` branch as
  target, otherwise it will be closed (this is selected by default).

# Contributing to Formatter Translations

Almost step provide by Behatch could be translated into your language with
`--lang` option. In order to fix/add translation, edit the appropriate file in
the `i18n` directory.

# Running tests

Make sure that you don't break anything with your changes by running the test
suites and the checks enforced by the CI:

```bash
$> composer install
$> ./bin/phpunit
$> php-cs-fixer fix --dry-run --diff
$> phpstan analyse
```

`php-cs-fixer` and `phpstan` are not project dependencies: install them globally, e.g.
`composer global require friendsofphp/php-cs-fixer phpstan/phpstan`.

The Behat suite needs the fixtures web server. `@javascript` scenarios also need a
`chromedriver` binary in your `PATH` (used by Panther):

```bash
$> php -S localhost:8080 -t tests/fixtures/www &
$> ./bin/behat                           # full suite (~2 min)
$> ./bin/behat --tags='~@javascript'     # without a browser (~40 s)
$> ./bin/behat tests/features/en         # one language (~25 s)
$> ./bin/behat --profile=panther         # every browser-compatible scenario in Chrome (~2 min)
```

If the port 8080 is already used, start the server on another port and set
`BEHAT_BASE_URL`, e.g. `BEHAT_BASE_URL=http://localhost:8089 ./bin/behat`.

If the port 9515 is already used by another chromedriver, set `BEHAT_CHROMEDRIVER_PORT`,
e.g. `BEHAT_CHROMEDRIVER_PORT=9516 ./bin/behat --profile=panther`.
