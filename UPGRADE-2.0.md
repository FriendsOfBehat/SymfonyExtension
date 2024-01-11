# Migrating from SymfonyExtension `2.x` to SymfonyExtension `2.4.2`

If you were having "friends-of-behat/mink-browserkit-driver" or "friends-of-behat/mink" in your composer.json, you should remove them and add "behat/mink-browserkit-driver" and "behat/mink" accordingly.

# Migrating from SymfonyExtension `1.x` to SymfonyExtension `2.0`

## Upgrade checklist

### Symfony application configuration

- Register `\FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle` bundle in your kernel in test environment.

- Load your context services definitions in `test` environment (check [installation documentation](docs/01_installation.md)).

### Behat extensions configuration

- Remove `FriendsOfBehat\CrossContainerExtension` and `FriendsOfBehat\ContextServiceExtension` from the extensions list.

- Integrate the configuration changes to your `FriendsOfBehat\SymfonyExtension` config (see [configuration reference](docs/05_configuration_reference.md)):

    - `kernel.env` was renamed to `kernel.environment`
    
    - `kernel.bootstrap` was moved to `bootstrap`
    
    - `env_file` setting was removed (use original Symfony bootstrap file or a custom one to load environment variables)

### Behat suite configuration

- Replace `contexts_services` configuration key from your suites configuration with `contexts`.

### Context service - definition

- Make sure all your context definitions are public.

- Remove `fob.context_service` tag from your context definitions.

### Context service - dependencies

- Remove `__symfony__.` and `__symfony_shared__.` prefixes from your context dependencies.

- Use `behat.mink.default_session` service instead of `mink.default_session` or getting the session from `__behat__.mink` service.

- Inject `behat.mink.parameters` service (which is an object implementing `\ArrayAccess`) instead of `%__behat__.mink.parameters%`
  parameter. Remove `array` typehint in the class implementation and assert it's `\ArrayAccess` or `array` instead.

## Exemplary upgrade

Sylius has updated from v1 to v2 in [this PR](https://github.com/Sylius/Sylius/pull/10102).

```xml
<!-- Before -->
<service id="mink.default_session" class="Behat\Mink\Session" lazy="true" public="false">
    <factory service="__behat__.mink" method="getSession" />
</service>

<service id="some_context" class="SomeContext" public="true">
    <argument type="service" id="mink.default_session" />
    <argument>%__behat__.mink.parameters%</argument>
    <argument type="service" id="__symfony__.some_service" />
    <tag name="fob.context_service" />
</service>

<!-- After -->
<service id="some_context" class="SomeContext" public="true">
    <argument type="service" id="behat.mink.default_session" />
    <argument type="service" id="behat.mink.parameters" />
    <argument type="service" id="some_service" />
</service>
```
