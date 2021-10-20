# Table of contents

 * [Installation](#installation)
 * [Usage](#usage)
 * [Mink integration](#mink-integration)
 * [Behat/Symfony2Extension differences](#differences-from-behatsymfony2extension)
 * [Configuration reference](#configuration-reference)

# Installation

If you're starting a new project, we recommend to use Symfony 4 with Flex as it's the most straightforward way.
If you're adding this extension to an existing project, pick the method that fits it the best. 

### Symfony 4/5 (with Flex)

1. Require this extension using *Composer* and allow for using contrib recipes:

```bash
composer require --dev friends-of-behat/symfony-extension:^2.0
```

### Symfony 4/5 (new directory structure, without Flex)

1. Require this extension using *Composer*:

```bash
composer require --dev friends-of-behat/symfony-extension:^2.0
```

2. Enable it within your Behat configuration:

```yaml
# behat.yaml.dist / behat.yaml

default:
    extensions:
        FriendsOfBehat\SymfonyExtension: ~
```

3. Register a helper bundle in your kernel:

```php
# config/bundles.php

return [
    // ...
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true],
];
```

4. Create `tests/Behat` directory for Behat-related classes:

```bash
mkdir -p tests/Behat
```

5. Set up autowiring and autoconfiguration for Behat-related services you'll create later:

```yaml
# config/services_test.yaml

services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Tests\Behat\:
        resource: '../tests/Behat/*'
```

### Symfony 3 (old directory structure)

1. Require this extension using *Composer*:

```bash
composer require --dev friends-of-behat/symfony-extension:^2.0
```

2. Enable it within your Behat configuration:

```yaml
# behat.yml.dist / behat.yml

default:
    extensions:
        FriendsOfBehat\SymfonyExtension: ~
```

3. Register a helper bundle in your kernel:

```php
# app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
    );
    
    if ('test' === $this->getEnvironment()) {
        $bundles[] = new \FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle();
    }
}
```

4. Create `tests/Behat` directory for Behat-related classes:

```bash
mkdir -p tests/Behat
```

5. Set up autowiring and autoconfiguration for Behat-related services you'll create later:

```yaml
# app/config/config_test.yml

# ...

services:
    _defaults:
        autowire: true
        autoconfigure: true

    Tests\Behat\:
        resource: '../../tests/Behat/*'
```

# Usage

This tutorial assumes you're using the new directory structure with autowiring and autoconfiguration enabled.
Let's first create a sample feature file (which is quite useless for demo purposes):

```gherkin
# features/using_symfony_extension.feature

Feature: Using SymfonyExtension

    Scenario: Checking the application's kernel environment
        Then the application's kernel should use "test" environment
``` 

There are two methods to check the kernel's environment - either by calling `getEnvironment()` method on the kernel itself 
or by injecting `%kernel.environment%` parameter.

We'll need also a dummy context implementation:

```php
<?php

# tests/Behat/DemoContext.php

namespace App\Tests\Behat;
// If using Symfony 3, use namespace "Tests\Behat" instead

use Behat\Behat\Context\Context;

final class DemoContext implements Context
{
    /**
     * @Then the application's kernel should use :expected environment 
     */
    public function kernelEnvironmentShouldBe(string $expected): void
    {
    }
}
```

And also a suite defined in Behat configuration:

```yaml
# behat.yaml.dist / behat.yaml

default:
    suites:
        default:
            contexts:
                - App\Tests\Behat\DemoContext

```

After running Behat, the scenario should be passing.

### Services injection

Modify the existing `DemoContext` to be able to inject a kernel instance:

```php
<?php

// ...

use Symfony\Component\HttpKernel\KernelInterface;

final class DemoContext implements Context
{
    /** @var KernelInterface */
    private $kernel;
    
    public function __construct(KernelInterface $kernel) 
    {
        $this->kernel = $kernel;
    }

    /**
     * @Then the application's kernel should use :expected environment 
     */
    public function kernelEnvironmentShouldBe(string $expected): void
    {
        if ($this->kernel->getEnvironment() !== $expected) {
            throw new \RuntimeException();
        }
    }
}
```

If you're using autowiring and autoconfiguration, that's all you need! After running Behat, you should see a passing scenario.

If you're not, you need to register your context as a public service and define its dependencies:

```yaml
# config/services_test.yaml (Symfony 4/5)
# app/config/config_test.yml (Symfony 3)

services:
    App\Tests\Behat\DemoContext:
        public: true
        arguments:
            - "@kernel"
```

### Parameters injection

Modify the existing `DemoContext` to be able to inject a kernel environment as a parameter:

```php
<?php

// ...

final class DemoContext implements Context
{
    /** @var string */
    private $environment;
    
    public function __construct(string $environment) 
    {
        $this->environment = $environment;
    }

    /**
     * @Then the application's kernel should use :expected environment 
     */
    public function kernelEnvironmentShouldBe(string $expected): void
    {
        if ($this->environment !== $expected) {
            throw new \RuntimeException();
        }
    }
}
```

If you're using autowiring and autoconfiguration, that's all you need! After running Behat, you should see a passing scenario.

If you're not, you need to register your context as a public service and define its dependencies:

```yaml
# config/services_test.yaml (Symfony 4/5)
# app/config/config_test.yml (Symfony 3)

services:
    App\Tests\Behat\DemoContext:
        public: true
        arguments:
            - "%kernel.environment%"
```

# Mink integration

_SymfonyExtension_ provides an integration with [Mink](https://github.com/minkphp/Mink) and defines a dedicated,
isolated driver to use for Symfony application testing.

### Installation

1. Require the packages needed for the driver using _Composer_:

```bash
composer require --dev friends-of-behat/mink friends-of-behat/mink-extension friends-of-behat/mink-browserkit-driver
```

_Those `friends-of-behat` packages are forks of the original ones, adding support for Symfony 5 and dropping support for Symfony <4.4._

2. Enable the bundled driver:

```yaml
# behat.yaml.dist / behat.yaml

default:
    extensions:
        # ...
        Behat\MinkExtension:
            sessions:
                symfony:
                    symfony: ~
```

### Usage

In order to use Mink, pass the Session to the constructor and call methods on it in the context.

```php
use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;

final class DemoContext implements Context
{
    /** @var Session */
    private $session;
    
    /** @var RouterInterface */
    private $router;

    public function __construct(Session $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @Then I visit some page 
     */
    public function visitSomePage(): void
    {
        $this->session->visit($this->router->generate('some_route'));
    }
}
```

_Calling any method on Mink-related services in the constructor is not permitted and will cause errors._

### Shared services

This integration provides the following services to use inside Symfony container:

 * **`behat.mink`** (autowired by `\Behat\Mink\Mink`) - the Mink service

 * **`behat.mink.default_session`** (autowired by `\Behat\Mink\Session`) - the default Mink session for the current scenario
 
 * **`behat.mink.parameters`** (autowired by `\FriendsOfBehat\SymfonyExtension\Mink\MinkParameters`) - an object 
 containing the configuration parameters of `MinkExtension` (implementing `\ArrayAccess` so that it can be treated as an array)
 
 * **`behat.driver.service_container`** - service container used by the `symfony` Mink driver, useful for assertions based on
  application state after a request has been handled

# Differences from Behat/Symfony2Extension

### Contexts as services

In _Behat/Symfony2Extension_ the dependencies of a context are defined in the Behat configuration file. In this extension,
contexts are defined as services - this makes reusing suites effortless, also allowing to support autowiring and autoconfiguration.

### Isolated driver

The Mink driver provided with this extension differs from the one provided with _Behat/Symfony2Extension_,
as it uses an isolated application kernel instance, so that services state changes within your contexts does not affect 
the driver results. With that limitation, changing the driver to a different one is seamless. For more information, look
at [this issue](https://github.com/Behat/Symfony2Extension/issues/112).

# Configuration reference

By default, if no confguration is passed, _SymfonyExtension_ will try its best to guess it.
The full configuration tree looks like that:

```yaml
# behat.yaml.dist / behat.yaml

default:
    extensions:
        FriendsOfBehat\SymfonyExtension:
            bootstrap: ~
            kernel:
                class: ~
                path: ~
                environment: ~
                debug: ~
```

 * **`bootstrap`**: 
 
    It is a path to the file requried once while the extension is loaded. You can use this file to set up your testing 
    environment - set some enviornment variables or preload an external file.
    If you do not pass any, it would look for either `config/bootstrap.php` (Symfony 4/5) or `app/autoload.php` (Symfony 3). 
    If none are found, no file would be loaded.
    
 * **`kernel.class`**:
 
    It is a fully qualified class name of the application kernel class.
    If you do not pass any, it would look for either `App\Kernel` (Symfony 4/5) or `AppKernel` (Symfony 3).
    If none are found, an exception would be thrown and you would be required to specify it explicitly.
    
 * **`kernel.path`**:
 
    It is a path to the file containing the application kernel class. You might want to set it if your kernel is not
    autoloaded by Composer's autoloaded.
    If `kernel.class` is not defined, it would automatically use `app/AppKernel.php` if `AppKernel` class was autoconfigured.
    
 * **`kernel.environment`**:
 
    It allows you to force using a given environment. If it is not set, it uses `APP_ENV` environment variable if defined
    or falls back to `test`.
    
 * **`kernel.debug`**:
 
    It allows you to force enabling or disabling debug mode. If it is not set, it uses `APP_DEBUG` environment variable 
    if defined or falls back to `true`.

## Enable the kernel environment **test**

To configure the environment used by the kernel (`APP_ENV`) while running scenarios, configure the extension:

```yaml
# behat.yaml.dist / behat.yaml

default:
    extensions:
        FriendsOfBehat\SymfonyExtension:
            kernel:
                environment: test
            bootstrap: tests/bootstrap.php
```
