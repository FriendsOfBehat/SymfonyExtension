## Installation

If you're starting a new project, we recommend to use Symfony 4 with Flex as it's the most straightforward way.
If you're adding this extension to an existing project, pick the method that fits it the best. 

### Symfony 4 (with Flex)

1. Require this extension using *Composer* and allow for using contrib recipes:

```bash
composer require --dev friends-of-behat/symfony-extension:^2.0
```

### Symfony 4 (new directory structure, without Flex)

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
