## Mink integration

*SymfonyExtension* provides an integration with [Mink](https://github.com/minkphp/Mink) and defines a dedicated,
isolated driver to use for Symfony application testing.

### Installation

1. Require the packages needed for the driver using *Composer*:

```bash
composer require --dev behat/mink-extension behat/mink-browserkit-driver
```

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

 * **`behat.mink.default_session`** (autowired by `\Behat\Mink\Session`) - the default Mink session for the current scenario
 
 * **`behat.mink.parameters`** (autowired by `\FriendsOfBehat\SymfonyExtension\Mink\MinkParameters`) - an object 
 containing the configuration parameters of `MinkExtension` (implementing `\ArrayAccess` so that it can be treated as an array)
