## Usage

This tutorial assumes you're using the new directory structure with autowiring and autoconfiguration enabled.
Let's first create a sample feature file (which is quite useless for demo purposes):

```gherkin
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
# behat.yml.dist / behat.yml

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
# config/services_test.yaml (Symfony 4)
# app/config/config_test.yml (Symfony 3)

services:
    App\Tests\DemoContext:
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
# config/services_test.yaml (Symfony 4)
# app/config/config_test.yml (Symfony 3)

services:
    App\Tests\DemoContext:
        public: true
        arguments:
            - "%kernel.environment%"
```
