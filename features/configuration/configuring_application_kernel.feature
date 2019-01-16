Feature: Configuring application kernel

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
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Symfony\Component\HttpKernel\KernelInterface;

        final class SomeContext implements Context {
            private $kernel;

            public function __construct(KernelInterface $kernel) { $this->kernel = $kernel; }

            /** @Then the application kernel should have environment :environment */
            public function kernelEnvironmentShouldBe(string $environment): void { assert($this->kernel->getEnvironment() === $environment); }

            /** @Then the application kernel should have debug :state*/
            public function kernelDebugShouldBe(string $state): void
            {
                $map = ['enabled' => true, 'disabled' => false];

                if (!array_key_exists($state, $map)) { throw new \Exception('Invalid state passed!'); }

                assert($this->kernel->isDebug() === $map[$state]);
            }

            /** @Then the server and environment variable :variable is :value */
            public function environmentVariableIs(string $variable, string $value): void
            {
                assert($_SERVER[$variable] === $value);
                assert($_ENV[$variable] === $value);
                assert(getenv($variable) === $value);
            }
        }
        """
        And a YAML services file containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
                arguments:
                    - "@kernel"
        """

    Scenario: Using test environment with debug enabled by default
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Then the application kernel should have environment "test"
                And the server and environment variable "APP_ENV" is "test"
                And the application kernel should have debug enabled
        """
        When I run Behat
        Then it should pass

    Scenario: Using environment based on Behat configuration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        environment: custom
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then the application kernel should have environment "custom"
        """
        When I run Behat
        Then it should pass

    Scenario: Using environment based on a server variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have environment "custom"
        """
        And a server variable "APP_ENV" set to "custom"
        When I run Behat
        Then it should pass

    Scenario: Using environment based on an environment variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have environment "custom"
        """
        And an environment variable "APP_ENV" set to "custom"
        When I run Behat
        Then it should pass

    Scenario: Using environment based on a server variable over an environment variable is also found
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have environment "custom_ser"
        """
        And a server variable "APP_ENV" set to "custom_ser"
        And an environment variable "APP_ENV" set to "custom_env"
        When I run Behat
        Then it should pass

    Scenario: Using environment based on Behat configuration over server or environment variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have environment "custom_conf"
        """
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        environment: custom_conf
        """
        And a server variable "APP_ENV" set to "custom_ser"
        And an environment variable "APP_ENV" set to "custom_env"
        When I run Behat
        Then it should pass

    Scenario: Using environment based on a server variable set in the bootstrap file
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have environment "custom"
        """
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    bootstrap: config/bootstrap.php
        """
        And a bootstrap file "config/bootstrap.php" containing:
        """
        <?php

        $_SERVER['APP_ENV'] = 'custom';
        """
        When I run Behat
        Then it should pass

    Scenario: Using debug based on Behat configuration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        debug: false
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        When I run Behat
        Then it should pass

    Scenario: Using debug based on a server variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        And a server variable "APP_DEBUG" set to "0"
        When I run Behat
        Then it should pass

    Scenario: Using debug based on an environment variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        And an environment variable "APP_DEBUG" set to "0"
        When I run Behat
        Then it should pass

    Scenario: Using debug based on a server variable over an environment variable is also found
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        And a server variable "APP_DEBUG" set to "0"
        And an environment variable "APP_DEBUG" set to "1"
        When I run Behat
        Then it should pass

    Scenario: Using debug based on Behat configuration over server or environment variable
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        debug: false
        """
        And a server variable "APP_DEBUG" set to "1"
        And an environment variable "APP_DEBUG" set to "1"
        When I run Behat
        Then it should pass

    Scenario: Using debug based on a server variable set in the bootstrap file
        Given a feature file containing:
        """
        Feature:
            Scenario:
                And the application kernel should have debug disabled
        """
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    bootstrap: config/bootstrap.php
        """
        And a bootstrap file "config/bootstrap.php" containing:
        """
        <?php

        $_SERVER['APP_DEBUG'] = '0';
        """
        When I run Behat
        Then it should pass
