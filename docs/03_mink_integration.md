## Mink integration

*SymfonyExtension* provides an integration with [Mink](https://github.com/minkphp/Mink) and defines a dedicated,
isolated driver to use for Symfony application testing.

### Installation

1. Require the packages needed for the driver using *Composer*:

```bash
composer require --dev friends-of-behat/mink friends-of-behat/mink-extension friends-of-behat/mink-browserkit-driver
```

_Those `friends-of-behat` packages are forks of the original ones, adding support for Symfony 5 and dropping support for Symfony <4.4._

2. Enable the bundled driver:

```yaml
# behat.yml.dist / behat.yml

default:
    extensions:
        # ...
        Behat\MinkExtension:
            sessions:
                symfony:
                    symfony: ~
```

### Usage

This integration provides two services to use inside Symfony container:

 * **`behat.mink`** (autowired by `\Behat\Mink\Mink`) - the Mink service

 * **`behat.mink.default_session`** (autowired by `\Behat\Mink\Session`) - the default Mink session for the current scenario
 
 * **`behat.mink.parameters`** (autowired by `\FriendsOfBehat\SymfonyExtension\Mink\MinkParameters`) - an object 
 containing the configuration parameters of `MinkExtension` (implementing `\ArrayAccess` so that it can be treated as an array)
