# Symfony Extension

Integrates Behat with Symfony (`^3.4` and `^4.1`).
 
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
