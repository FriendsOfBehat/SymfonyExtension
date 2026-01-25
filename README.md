<p align="center">
    <img src="https://avatars2.githubusercontent.com/u/20600343" /><br/>
</p>

<h1 align="center">SymfonyExtension</h1>

> **Fork Notice**: This is a maintained fork of [FriendsOfBehat/SymfonyExtension](https://github.com/FriendsOfBehat/SymfonyExtension) with improved architecture and extended version support.

This Behat extension provides an integration with Symfony (`^6.4`, `^7.0`, and `^8.0`) and Mink driver for Symfony applications. Also supports Behat 4.

It allows for:

 * defining your contexts as regular Symfony services

 * autowiring and autoconfiguring your contexts

 * testing your Symfony application without having to set up a server

## What's Different in This Fork

| Feature | Original | This Fork |
|---------|----------|-----------|
| Symfony support | 6.4, 7.0 | 6.4, 7.0, **8.0** |
| Behat support | 3.x | 3.x, **4.x** |
| Kernel management | Dual-kernel, tightly coupled | **KernelManager** with lazy driver kernel |
| Driver kernel | Always created | **Lazy-loaded** (only when Mink used) |

### Architecture Improvements

- **KernelManager**: Centralized lifecycle management for context and driver kernels
- **Lazy driver kernel**: Only instantiated when Mink makes first HTTP request
- **Cleaner separation**: Explicit `setUp()`/`tearDown()` lifecycle hooks

## Installation

```bash
composer require yannickgranger/symfony-extension --dev
```

## Migration from FriendsOfBehat/SymfonyExtension

This is a **drop-in replacement**. No configuration changes needed:

```bash
# Remove old package
composer remove friends-of-behat/symfony-extension

# Install this fork
composer require yannickgranger/symfony-extension --dev
```

Your existing `behat.yml` configuration will work as-is.

## Documentation

 * [Installation](DOCUMENTATION.md#installation)
 * [Usage](DOCUMENTATION.md#usage)
 * [Mink integration](DOCUMENTATION.md#mink-integration)
 * [Behat/Symfony2Extension differences](DOCUMENTATION.md#differences-from-behatsymfony2extension)
 * [Configuration reference](DOCUMENTATION.md#configuration-reference)

For a bit of backstory, take a look at the [SymfonyExtension v2.0.0 release blogpost](https://kamilkokot.com/tame-behat-with-the-brand-new-symfony-extension).

## Versioning and release cycle

This package follows [semantic versioning](https://semver.org/).

Public API is defined as configuration and behaviour defined in form of testable scenarios in the [`features`](features) directory.
This means once your application is configured to use Behat with SymfonyExtension,
it shall continue to work flawlessly within the same major version.

## License

This extension is completely free and released under permissive [MIT license](LICENSE).

## Authors

Originally created by [Kamil Kokot](https://github.com/pamil).
See the list of [all contributors](https://github.com/FriendsOfBehat/SymfonyExtension/graphs/contributors).

Fork maintained by [Yannick Granger](https://github.com/yannickgranger).
