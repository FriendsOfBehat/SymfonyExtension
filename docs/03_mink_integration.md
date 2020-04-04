## Mink integration

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
