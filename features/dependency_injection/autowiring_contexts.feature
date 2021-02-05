Feature: Autowiring contexts

    Background:
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
        """

    Scenario: Autowiring a context with a service
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Then the container should be passed
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Psr\Container\ContainerInterface;

        final class SomeContext implements Context {
            private $container;

            public function __construct(?ContainerInterface $container = null) { $this->container = $container; }

            /** @Then the container should be passed */
            public function containerShouldBePassed(): void
            {
                assert(is_object($this->container));
                assert($this->container instanceof ContainerInterface);
            }
        }
        """
        And a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true

                App\Tests\SomeContext:
                    public: true
            """
        When I run Behat
        Then it should pass

    Scenario: Autowiring a context with a binding
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Then the passed argument should be "KrzysztofKrawczyk"
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $argument;

            public function __construct(?string $argument = null) { $this->argument = $argument; }

            /** @Then the passed argument should be :expected */
            public function passedArgumentShouldBe(string $expected): void { assert($this->argument === $expected); }
        }
        """
        And a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true
                    bind:
                        $argument: KrzysztofKrawczyk

                App\Tests\SomeContext:
                    public: true
            """
        When I run Behat
        Then it should pass

    Scenario: Autowiring and autoconfiguring context based on prototype
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Then the container should be passed
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Psr\Container\ContainerInterface;

        final class SomeContext implements Context {
            private $container;

            public function __construct(?ContainerInterface $container = null) { $this->container = $container; }

            /** @Then the container should be passed */
            public function containerShouldBePassed(): void
            {
                assert(is_object($this->container));
                assert($this->container instanceof ContainerInterface);
            }
        }
        """
        And a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true
                    autoconfigure: true

                App\Tests\:
                    resource: '../tests/*'
            """
        When I run Behat
        Then it should pass

    Scenario: Autowiring a context with test client
        Given a working Symfony application with SymfonyExtension configured
        And an environment variable "APP_ENV" set to "test"
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then the client should be passed
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Symfony\Component\BrowserKit\AbstractBrowser;

        final class SomeContext implements Context
        {
            private $client;

            public function __construct(AbstractBrowser $client) { $this->client = $client; }

            /** @Then the client should be passed */
            public function clientShouldBePassed(): void
            {
                assert(is_object($this->client));
                assert($this->client instanceof AbstractBrowser);
            }
        }
        """
        And a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true
                    autoconfigure: true
                    bind:
                        $client: '@test.client'

                App\Tests\:
                    resource: '../tests/*'
            """
        When I run Behat
        Then it should pass