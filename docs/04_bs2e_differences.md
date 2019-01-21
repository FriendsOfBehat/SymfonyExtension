## Differences from Behat/Symfony2Extension

### Contexts as services

In *Behat/Symfony2Extension* the dependencies of a context are defined in the Behat configuration file. In this extension,
contexts are defined as services - this makes reusing suites effortless, also allowing to support autowiring and autoconfiguration.

### Isolated driver

The Mink driver provided with this extension differs from the one provided with *Behat/Symfony2Extension*,
as it uses an isolated application kernel instance, so that services state changes within your contexts does not affect 
the driver results. With that limitation, changing the driver to a different one is seamless. For more information, look
at [this issue](https://github.com/Behat/Symfony2Extension/issues/112).
