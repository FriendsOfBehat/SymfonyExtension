# Symfony Extension [![License](https://img.shields.io/packagist/l/friends-of-behat/symfony-extension.svg)](https://packagist.org/packages/friends-of-behat/symfony-extension) [![Version](https://img.shields.io/packagist/v/friends-of-behat/symfony-extension.svg)](https://packagist.org/packages/friends-of-behat/symfony-extension) [![Build status on Linux](https://img.shields.io/travis/FriendsOfBehat/SymfonyExtension/master.svg)](http://travis-ci.org/FriendsOfBehat/SymfonyExtension) [![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/FriendsOfBehat/SymfonyExtension.svg)](https://scrutinizer-ci.com/g/FriendsOfBehat/SymfonyExtension/)

Integrates Behat with Symfony (both 2 and 3). 
Inspired by [Behat/Symfony2Extension](https://github.com/Behat/Symfony2Extension).

## Differences

 -  Built-in `symfony` driver uses different kernel than the one that is used in the contexts.
This means you can always change it to any other driver without any issues and 
ensures that application behaviour will not be affected by stateful services.

## Usage

1. Install it:

    ```bash
    $ composer require friends-of-behat/symfony-extension --dev
    ```

2. Enable and configure in your Behat configuration:

    ```yaml
    # behat.yml
    default:
        # ...
        extensions:
            FriendsOfBehat\SymfonyExtension: ~
    ```

**Symfony 3 configuration**

```
FriendsOfBehat\SymfonyExtension:
    kernel:
        bootstrap: 'var/bootstrap.php.cache'
        path: app/AppKernel.php
        class: 'AppKernel'
        env: test
        debug: true
```

**Symfony 4 configuration**

```
FriendsOfBehat\SymfonyExtension:
    env_file: .env
    kernel:
        class: 'MyTrip\Kernel'
        path: src/Kernel.php
        debug: true
```

Symfony 4 does not have bootstrap file anymore and the environment is configured in the .env file.

3. Good luck & have fun!
