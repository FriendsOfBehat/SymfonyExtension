# CHANGELOG FOR `2.1.x`

## v2.2.0 (2021-02-04)

#### TL;DR

- **Added support for PHP 8** ([#134](https://github.com/FriendsOfBehat/SymfonyExtension/issues/134))

#### Details

- [#121](https://github.com/FriendsOfBehat/SymfonyExtension/issues/121) Require PHP ^7.2 ([@pamil](https://github.com/pamil))
- [#130](https://github.com/FriendsOfBehat/SymfonyExtension/issues/130) Missing class for Symfony 5 ([@cv65kr](https://github.com/cv65kr), [@pamil](https://github.com/pamil))
- [#134](https://github.com/FriendsOfBehat/SymfonyExtension/issues/134) Add support for PHP 8 ([@dunglas](https://github.com/dunglas))
- [#136](https://github.com/FriendsOfBehat/SymfonyExtension/issues/136) Bump dependencies to Symfony ^4.4|^5.1 ([@pamil](https://github.com/pamil))
- [#137](https://github.com/FriendsOfBehat/SymfonyExtension/issues/137) Bump dependencies to PHP ^7.3|^8.0 ([@pamil](https://github.com/pamil))
- [#139](https://github.com/FriendsOfBehat/SymfonyExtension/issues/139) Switch from Travis to GitHub Actions ([@pamil](https://github.com/pamil))
- [#140](https://github.com/FriendsOfBehat/SymfonyExtension/issues/140) Upgrade to Psalm v4.1.1 ([@pamil](https://github.com/pamil))
- [#141](https://github.com/FriendsOfBehat/SymfonyExtension/issues/141) Fix the build for PHP 8 ([@pamil](https://github.com/pamil))

## v2.1.0 (2020-04-04)

#### TL;DR

- **Added support for Symfony 5** ([#100](https://github.com/FriendsOfBehat/SymfonyExtension/issues/100))
- **Added support for PHP 7.4** ([#107](https://github.com/FriendsOfBehat/SymfonyExtension/issues/107))
- Added integration with FriendsOfBehat/PageObjectExtension ([#105](https://github.com/FriendsOfBehat/SymfonyExtension/issues/105))
- Added integration with BrowserKit ([#82](https://github.com/FriendsOfBehat/SymfonyExtension/issues/82))
- Exposed Mink service ([#69](https://github.com/FriendsOfBehat/SymfonyExtension/issues/69))
- Exposed Mink's driver service container ([#116](https://github.com/FriendsOfBehat/SymfonyExtension/issues/116))
- Removed support for Symfony 3.4 ([#118](https://github.com/FriendsOfBehat/SymfonyExtension/issues/118))

#### Details

- [#60](https://github.com/FriendsOfBehat/SymfonyExtension/issues/60) Remove support for Symfony 4.1 as it's EOLed ([@pamil](https://github.com/pamil))
- [#69](https://github.com/FriendsOfBehat/SymfonyExtension/issues/69) Expose the Mink service ([@pamil](https://github.com/pamil))
- [#82](https://github.com/FriendsOfBehat/SymfonyExtension/issues/82) Provide simple BrowserKit integration ([@pamil](https://github.com/pamil))
- [#98](https://github.com/FriendsOfBehat/SymfonyExtension/issues/98) Add Symfony 4.3.* build to Travis CI ([@pamil](https://github.com/pamil))
- [#100](https://github.com/FriendsOfBehat/SymfonyExtension/issues/100) Add support for Symfony 4.4 and 5.0; remove for 4.2 and 4.3 ([@pamil](https://github.com/pamil))
- [#105](https://github.com/FriendsOfBehat/SymfonyExtension/issues/105) Add integration with FriendsOfBehat/PageObjectExtension ([@pamil](https://github.com/pamil))
- [#107](https://github.com/FriendsOfBehat/SymfonyExtension/issues/107) Add support for PHP 7.4 ([@pamil](https://github.com/pamil))
- [#108](https://github.com/FriendsOfBehat/SymfonyExtension/issues/108) Add compatibility with Symfony 5 ([@pamil](https://github.com/pamil))
- [#114](https://github.com/FriendsOfBehat/SymfonyExtension/issues/114) Remove conflict with Symplify libraries ([@pamil](https://github.com/pamil))
- [#115](https://github.com/FriendsOfBehat/SymfonyExtension/issues/115) Add safety check for SymfonyDriverFactory to make sure BrowserKitDriver is installed ([@pamil](https://github.com/pamil))
- [#116](https://github.com/FriendsOfBehat/SymfonyExtension/issues/116) Accessing tested application services easily via driver's service container ([@pamil](https://github.com/pamil))
- [#117](https://github.com/FriendsOfBehat/SymfonyExtension/issues/117) Mention friends-of-behat forks of Mink-related repositories in the docs ([@pamil](https://github.com/pamil))
- [#118](https://github.com/FriendsOfBehat/SymfonyExtension/issues/118) Remove support for Symfony 3.4 ([@pamil](https://github.com/pamil))
- [#119](https://github.com/FriendsOfBehat/SymfonyExtension/issues/119) Fix a weird bug causing failures in Sylius ([@pamil](https://github.com/pamil))
- [#120](https://github.com/FriendsOfBehat/SymfonyExtension/issues/120) Recommend Mink forks in suggests section of composer.json ([@pamil](https://github.com/pamil))

## v2.1.0-RC.2 (2020-04-04)

- [#114](https://github.com/FriendsOfBehat/SymfonyExtension/issues/114) Remove conflict with Symplify libraries ([@pamil](https://github.com/pamil))
- [#120](https://github.com/FriendsOfBehat/SymfonyExtension/issues/120) Recommend Mink forks in suggests section of composer.json ([@pamil](https://github.com/pamil))

## v2.1.0-RC.1 (2020-04-04)

- [#119](https://github.com/FriendsOfBehat/SymfonyExtension/issues/119) Fix a weird bug causing failures in Sylius ([@pamil](https://github.com/pamil))

## v2.1.0-BETA.2 (2020-04-04)

- [#107](https://github.com/FriendsOfBehat/SymfonyExtension/issues/107) Add support for PHP 7.4 ([@pamil](https://github.com/pamil))
- [#115](https://github.com/FriendsOfBehat/SymfonyExtension/issues/115) Add safety check for SymfonyDriverFactory to make sure BrowserKitDriver is installed ([@pamil](https://github.com/pamil))
- [#116](https://github.com/FriendsOfBehat/SymfonyExtension/issues/116) Accessing tested application services easily via driver's service container ([@pamil](https://github.com/pamil))
- [#117](https://github.com/FriendsOfBehat/SymfonyExtension/issues/117) Mention friends-of-behat forks of Mink-related repositories in the docs ([@pamil](https://github.com/pamil))
- [#118](https://github.com/FriendsOfBehat/SymfonyExtension/issues/118) Remove support for Symfony 3.4 ([@pamil](https://github.com/pamil))

## v2.1.0-BETA.1 (2020-01-15)

- [#60](https://github.com/FriendsOfBehat/SymfonyExtension/issues/60) Remove support for Symfony 4.1 as it's EOLed ([@pamil](https://github.com/pamil))
- [#69](https://github.com/FriendsOfBehat/SymfonyExtension/issues/69) Expose the Mink service ([@pamil](https://github.com/pamil))
- [#82](https://github.com/FriendsOfBehat/SymfonyExtension/issues/82) Provide simple BrowserKit integration ([@pamil](https://github.com/pamil))
- [#83](https://github.com/FriendsOfBehat/SymfonyExtension/issues/83) Fix master build ([@pamil](https://github.com/pamil))
- [#87](https://github.com/FriendsOfBehat/SymfonyExtension/issues/87) Update README.md ([@DonCallisto](https://github.com/DonCallisto))
- [#98](https://github.com/FriendsOfBehat/SymfonyExtension/issues/98) Add Symfony 4.3.* build to Travis CI ([@pamil](https://github.com/pamil))
- [#100](https://github.com/FriendsOfBehat/SymfonyExtension/issues/100) Add support for Symfony 4.4 and 5.0; remove for 4.2 and 4.3 ([@pamil](https://github.com/pamil))
- [#105](https://github.com/FriendsOfBehat/SymfonyExtension/issues/105) Add integration with FriendsOfBehat/PageObjectExtension ([@pamil](https://github.com/pamil))
- [#106](https://github.com/FriendsOfBehat/SymfonyExtension/issues/106) Fix the build ([@pamil](https://github.com/pamil))
- [#108](https://github.com/FriendsOfBehat/SymfonyExtension/issues/108) Add compatibility with Symfony 5 ([@pamil](https://github.com/pamil))

# CHANGELOG FOR `2.0.x`

## v2.0.11 (2020-04-04)

- [#106](https://github.com/FriendsOfBehat/SymfonyExtension/issues/106) Fix the build ([@pamil](https://github.com/pamil))
- [#114](https://github.com/FriendsOfBehat/SymfonyExtension/issues/114) Remove conflict with Symplify libraries ([@pamil](https://github.com/pamil))

## v2.0.10 (2019-12-09)

- [#101](https://github.com/FriendsOfBehat/SymfonyExtension/issues/101) Fix referencing context initializers ([@kamazee](https://github.com/kamazee))
- [#102](https://github.com/FriendsOfBehat/SymfonyExtension/issues/102) Fix the build & add tests for Symfony 4.3 and 4.4 ([@pamil](https://github.com/pamil))

## v2.0.9 (2019-10-10)

- [#77](https://github.com/FriendsOfBehat/SymfonyExtension/issues/77) Fix docs: change 'kernel.file' to 'kernel.path' ([@mkilmanas](https://github.com/mkilmanas))
- [#78](https://github.com/FriendsOfBehat/SymfonyExtension/issues/78) Use right namespace for service definition ([@alanpoulain](https://github.com/alanpoulain))
- [#84](https://github.com/FriendsOfBehat/SymfonyExtension/issues/84) Minor improvements to CI config ([@pamil](https://github.com/pamil))
- [#92](https://github.com/FriendsOfBehat/SymfonyExtension/issues/92) Improve Mink installation docs ([@pamil](https://github.com/pamil))
- [#93](https://github.com/FriendsOfBehat/SymfonyExtension/issues/93) [HotFix] Force object typehint ([@lchrusciel](https://github.com/lchrusciel))
- [#96](https://github.com/FriendsOfBehat/SymfonyExtension/issues/96) Add base_url to docs for configuring Mink ([@liquorvicar](https://github.com/liquorvicar))
- [#97](https://github.com/FriendsOfBehat/SymfonyExtension/issues/97) Do not require base URL set with Mink ([@pamil](https://github.com/pamil))

## v2.0.8 (2019-03-21)

- [#76](https://github.com/FriendsOfBehat/SymfonyExtension/issues/76) Initialize contexts registered as services ([@pamil](https://github.com/pamil))

## v2.0.7 (2019-03-17)

- [#75](https://github.com/FriendsOfBehat/SymfonyExtension/issues/75) Hotfix for weird bug in Sylius ([@pamil](https://github.com/pamil))

## v2.0.6 (2019-03-15)

- [#74](https://github.com/FriendsOfBehat/SymfonyExtension/issues/74) Fix bug preventing changes of Mink default session service ([@pamil](https://github.com/pamil))

## v2.0.5 (2019-03-04)

- [#72](https://github.com/FriendsOfBehat/SymfonyExtension/issues/72) Allow accessing a context in another context ([@pamil](https://github.com/pamil))

## v2.0.4 (2019-02-13)

- [#68](https://github.com/FriendsOfBehat/SymfonyExtension/issues/68) Better compatibility with Behat itself ([@pamil](https://github.com/pamil), [@alanpoulain](https://github.com/alanpoulain))

## v2.0.3 (2019-02-07)

Removed the possibility to autoconfigure `$minkParameters` without a typehint due to an instable implementation in Symfony.

- [#64](https://github.com/FriendsOfBehat/SymfonyExtension/issues/64) Fix typo ([@rogamoore](https://github.com/rogamoore))
- [#66](https://github.com/FriendsOfBehat/SymfonyExtension/issues/66) Revert mink parameters autoconfiguration ([@pamil](https://github.com/pamil))

## v2.0.2 (2019-01-30)

- [#57](https://github.com/FriendsOfBehat/SymfonyExtension/issues/57) Fix support for context initializers ([@pamil](https://github.com/pamil))
- [#58](https://github.com/FriendsOfBehat/SymfonyExtension/issues/58) Add support for PHP 7.3 ([@pamil](https://github.com/pamil))
- [#59](https://github.com/FriendsOfBehat/SymfonyExtension/issues/59) Fix Symfony 4.1 build ([@pamil](https://github.com/pamil))

## v2.0.1 (2019-01-24)

- [#55](https://github.com/FriendsOfBehat/SymfonyExtension/issues/55) Require symfony/dependency-injection ^3.4.10|^4.1 ([@pamil](https://github.com/pamil))
