<p align="center">
    <img src="https://avatars2.githubusercontent.com/u/20600343" /><br/>
</p>

<h1 align="center">SymfonyExtension</h1>

This Behat extension provides an integration with Symfony (`^6.0` and `^7.0`) and Mink driver for Symfony application.

It allows for:

 * defining your contexts as regular Symfony services
 
 * autowiring and autoconfiguring your contexts
 
 * testing your Symfony application without having to set up a server
 
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
 
Next major releases are not planned yet. Minor and patch releases will be published as needed.

Bug fixes will be provided only for the most recent minor release.
Security fixes will be provided for one year since the release of subsequent minor release.

Example (with arbitrary dates):

 - `v1.0.0` is released on _23.11.2019_
 - `v1.0.1` with a bugfix is provided on _03.02.2020_
 - `v1.1.0` is released on _15.02.2020_:
   - `1.0` branch will not get bugfixes anymore
   - `1.0` security support will end on _15.02.2021_
 
## License

This extension is completely free and released under permissive [MIT license](LICENSE).

## Authors

It is originally created by [Kamil Kokot](https://github.com/pamil). 
See the list of [all contributors](https://github.com/FriendsOfBehat/SymfonyExtension/graphs/contributors). 
