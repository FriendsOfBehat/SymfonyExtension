# Upgrade Guide: Behat 3 to Behat 4

This guide documents the changes required to upgrade from Behat 3 to Behat 4 for Symfony projects using the `friends-of-behat/symfony-extension`.

## Why This Guide Exists

Behat 4 introduces breaking changes that require modifications to both the extension configuration and your test context classes. This document captures the exact steps needed based on upgrading the OrgaMarco project.

---

## 1. Install a Behat Symfony Extension Fork

The official `friends-of-behat/symfony-extension` only supports up to Symfony 7. For Symfony 8 support, use the community fork:

```bash
# Add to composer.json repositories
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:bytes-commerce/behat-symfony-extension.git"
    }
]

# Require the fork
composer require --dev friends-of-behat/symfony-extension:dev-symfony-8-behat-4-support -W
```

**Fork repository**: `git@github.com:bytes-commerce/behat-symfony-extension.git`

**Fork changes**:
- Updated `behat/behat` constraint: `^3.22 || ^4.0`
- Updated `symfony/dependency-injection`: `^6.4 || ^7.0 || ^8.0`
- Updated `symfony/http-kernel`: `^6.4 || ^7.0 || ^8.0`

---

## 2. Use PHP 8 Attributes for Step Definitions

### The Critical Change

**Behat 4 uses `AttributeContextReader` which requires PHP 8 attributes**, not PHPDoc annotations.

All step definitions must be converted from:

```php
/**
 * @Given I am authenticated as :username
 */
public function iAmAuthenticatedAs(string $username): void
```

To:

```php
#[Given('I am authenticated as :username')]
public function iAmAuthenticatedAs(string $username): void
```

### Import Statements

Add the appropriate import for each attribute type:

```php
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
```

### Multiple Annotations → Multiple Attributes

When a method had multiple annotations (e.g., for different phrasings):

```php
// BEFORE (Behat 3)
/**
 * @Given I do something
 * @Given I do something else
 */
public function iDoSomething(): void

// AFTER (Behat 4)
#[Given('I do something')]
#[Given('I do something else')]
public function iDoSomething(): void
```

### Important Notes

- **Only convert `@Given`, `@When`, `@Then` annotations** - leave `@BeforeScenario`, `@AfterScenario`, and other hooks as PHPDoc annotations
- **Do not convert other PHPDoc** like `@param`, `@return`, etc.
- The attribute syntax uses **placeholders** like `:username` instead of regex patterns like `([^"]*)`

---

## 3. Update Service Configuration

### Tag Change: `fob.context` → `context.initializer`

If you were manually tagging contexts in `services.yaml`:

```yaml
# BEFORE (Behat 3)
services:
    _instanceof:
        Behat\Behat\Context\Context:
            tags: ['fob.context']
```

```yaml
# AFTER (Behat 4)
services:
    _instanceof:
        Behat\Behat\Context\Context:
            tags: ['context.initializer']
```

### Symfony Autoconfiguration (Recommended)

Instead of manual tagging, use Symfony's `registerForAutoconfiguration`:

```php
// In your extension's process() method or services configuration
$container->registerForAutoconfiguration(Context::class)
    ->addTag('context.initializer');
```

### Context Service Environment Handler Changes

The `ContextServiceEnvironmentHandler` in the fork handles two scenarios:

1. **Context is a Symfony service**: Retrieved from the container
2. **Context is not a service**: Instantiated directly using default constructor

```php
// buildEnvironment() handles both cases
if ($this->getContainer()->has($serviceId)) {
    $service = $this->getContainer()->get($serviceId);
    $symfonyContexts[$serviceId] = get_class($service);
} elseif (class_exists($serviceId)) {
    // Instantiate directly - will use default constructor or service injection if available
    $service = new $serviceId();
    if ($service !== null) {
        $symfonyContexts[$serviceId] = $serviceId;
    }
}
```

---

## 4. Update Context Initializers

The `context.initializer` tag is now used instead of `fob.context`. Ensure your `ContextServiceEnvironmentHandler` uses the correct constant:

```php
// Use ContextExtension::INITIALIZER_TAG
use Behat\Context\Extension\ContextExtension;

// In isolateEnvironment():
$isolatedEnvironment->registerContext($context);
```

---

## 5. Known Compatibility Issues

### MagicSetterTrait Typo

If you see an error like:
```
Class "App\Tests\Behat\User\MagicSetterTraits" not found
```

Check for a typo in trait names. The correct name is `MagicSetterTrait` (singular).

### Duplicate Import Statements

When converting, ensure you don't create duplicate imports:

```php
// WRONG - duplicate import
use Behat\Step\Given;
use Behat\Step\Given;  // Duplicate!

// CORRECT
use Behat\Step\Given;
```

---

## 6. Debugging Tips

### Check Behat Version

```bash
vendor/bin/behat --version
```

### Dry Run to Verify Step Definitions

```bash
vendor/bin/behat --dry-run
```

This shows which step definitions are matched without executing them.

### List All Step Definitions

```bash
vendor/bin/behat --definitions
```

### Verify Context Registration

Add temporary debug output in `ContextServiceEnvironmentHandler`:

```php
public function registerContext(Context $context): void
{
    $this->contexts[get_class($context)] = $context;
    // Uncomment to debug:
    // file_put_contents('/tmp/behat_debug.log', get_class($context) . "\n", FILE_APPEND);
}
```

---

## 7. Test Database Setup

Behat tests require a properly set up test database:

```bash
# Drop and recreate
APP_ENV=test php bin/console doctrine:database:drop --force
APP_ENV=test php bin/console doctrine:database:create

# Run migrations or schema create
APP_ENV=test php bin/console doctrine:migrations:migrate
# OR
APP_ENV=test php bin/console doctrine:schema:create
```

---

## 8. Running Behat Tests

```bash
# Run all tests (excluding WIP)
APP_ENV=test vendor/bin/behat --tags='~@wip'

# Dry run
APP_ENV=test vendor/bin/behat --dry-run

# Specific feature
APP_ENV=test vendor/bin/behat path/to/feature.feature
```

---

## Summary of Changes

| Area | Behat 3 | Behat 4 |
|------|---------|----------|
| Step definitions | PHPDoc `@Given` | PHP 8 `#[Given]` |
| Context tag | `fob.context` | `context.initializer` |
| Reader | `AnnotationContextReader` | `AttributeContextReader` |
| Symfony support | Up to Symfony 7 | Via fork: Symfony 8 |

---

## Files Modified During Upgrade

Key files that were modified in the OrgaMarco project:

- `composer.json` - Added fork repository and dependency
- `src/tests/Behat/**/*.php` - Converted ~368 step definitions across 35+ files
- `bc/behat-symfony-extension/src/Context/Environment/Handler/ContextServiceEnvironmentHandler.php` - Fork modifications
- `bc/behat-symfony-extension/src/Bundle/DependencyInjection/FriendsOfBehatSymfonyExtensionExtension.php` - Tag changes

---

## References

- [Behat 4 Changelog](https://github.com/Behat/Behat/blob/master/CHANGELOG.md)
- [SymfonyExtension Fork](https://github.com/bytes-commerce/behat-symfony-extension)
- [Behat PHP 8 Attributes Documentation](https://docs.behat.org/en/latest/guides/1.gherkin.html#step definitions)
