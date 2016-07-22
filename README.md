# Symfony Extension [![License](https://img.shields.io/packagist/l/friends-of-behat/symfony-extension.svg)](https://packagist.org/packages/friends-of-behat/symfony-extension) [![Version](https://img.shields.io/packagist/v/friends-of-behat/symfony-extension.svg)](https://packagist.org/packages/friends-of-behat/symfony-extension) [![Build status on Linux](https://img.shields.io/travis/FriendsOfBehat/SymfonyExtension/master.svg)](http://travis-ci.org/FriendsOfBehat/SymfonyExtension) [![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/FriendsOfBehat/SymfonyExtension.svg)](https://scrutinizer-ci.com/g/FriendsOfBehat/SymfonyExtension/)

Integrates Behat with Symfony (both 2 and 3).

## Usage

1. Install it:

```bash
$ composer require friends-of-behat/symfony-extension --dev
```

2. Enable and configure in your Behat configuration:

```yaml
default:
    # ...
    extensions:
        FriendsOfBehat\SymfonyExtension: Z
```

3. Good luck & have fun!
