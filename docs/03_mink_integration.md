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
 
 * **`behat.mink.parameters`** (autoconfigured by `$minkParameters`) - an array (`\ArrayAccess` object) containing the 
 configuration parameters of `MinkExtension`

### Caveats

This driver behaviour differs from the one provided with [`Behat/Symfony2Extension`](https://github.com/Behat/Symfony2Extension),
as it uses an isolated application kernel instance, so that services state changes within your contexts does not affect 
the driver results. With that limitation, changing the driver to a different one is seamless. For more information, look
at [this issue](https://github.com/Behat/Symfony2Extension/issues/112). 
