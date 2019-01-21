<p align="center">
    <img src="https://avatars2.githubusercontent.com/u/20600343" /><br/>
</p>

<h1 align="center">SymfonyExtension</h1>

This Behat extension provides an integration with Symfony (both `^3.4` and `^4.1`) and Mink driver for Symfony application.

It allows for:

 * defining your contexts as regular Symfony services
 
 * autowiring and autoconfiguring your contexts
 
 * testing your Symfony application without having to set up a server
 
## Documentation

 * [Installation](docs/01_installation.md)
 * [Usage](docs/02_usage.md)
 * [Mink integration](docs/03_mink_integration.md)
 * [Behat/Symfony2Extension differences](docs/04_bs2e_differences.md)
 * [Configuration reference](docs/05_configuration_reference.md)
 
## Versioning

This package follows [semantic versioning](https://semver.org/) - public API is defined as configuration and behaviour
defined in form of testable scenarios in the [`features`](features) directory. This means once your application is
configured to use Behat with SymfonyExtension, it shall continue to work flawlessly within the same major version.
PHP classes, apart from `FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle`, are not covered
by this backwards compatibility promise.
 
## License

This extension is completely free and released under permissive [MIT license](LICENSE).

## Authors

It is originally created by [Kamil Kokot](https://github.com/pamil). 
See the list of [all contributors](https://github.com/FriendsOfBehat/SymfonyExtension/graphs/contributors). 
