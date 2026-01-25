# Contributing to SymfonyExtension

Thank you for considering contributing to this fork of SymfonyExtension.

## Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yannickgranger/SymfonyExtension.git
   cd SymfonyExtension
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run the test suite:
   ```bash
   composer test
   ```

## Running Tests

This project uses **meta-testing**: Behat tests that run Behat inside temporary Symfony applications. Each scenario bootstraps a fresh Symfony app, configures it, runs Behat against it, and verifies the results.

```bash
# Run all tests
composer test

# Run tests with verbose output
vendor/bin/behat -f progress --strict -vvv

# Run a specific feature
vendor/bin/behat features/configuration/kernel_reboot.feature
```

## Code Style

We use [EasyCodingStandard](https://github.com/easy-coding-standard/easy-coding-standard) for code formatting.

```bash
# Check code style
vendor/bin/ecs check src tests

# Fix code style automatically
composer fix
```

## Static Analysis

We use [Psalm](https://psalm.dev/) for static analysis:

```bash
vendor/bin/psalm src --no-progress
```

## Pull Request Process

1. **Fork** the repository and create a feature branch from `master`
2. **Write tests** for your changes (if applicable)
3. **Ensure all checks pass**:
   ```bash
   composer analyse  # Validates composer.json, runs ECS and Psalm
   composer test     # Runs Behat tests
   ```
4. **Submit a PR** with a clear description of the changes

### PR Guidelines

- Keep changes focused and atomic
- Follow existing code style
- Add/update tests for new features or bug fixes
- Update documentation if needed

## Reporting Issues

When reporting bugs, please include:

- PHP version
- Symfony version
- Behat version
- Steps to reproduce
- Expected vs actual behavior
- Relevant configuration (behat.yml)

## Feature Requests

Feature requests are welcome. Please describe:

- The use case / problem you're solving
- Your proposed solution
- Any alternatives you've considered

## Architecture Notes

### Kernel Management

The extension uses a `KernelManager` that handles two separate kernel instances:

- **Context kernel**: For dependency injection into Behat contexts
- **Driver kernel**: For Mink/BrowserKit HTTP requests (lazy-loaded)

### Key Components

- `src/ServiceContainer/SymfonyExtension.php` - Behat extension entry point
- `src/Driver/Factory/SymfonyDriverFactory.php` - Mink driver factory
- `src/Kernel/KernelManager.php` - Kernel lifecycle management

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
