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
                And the application kernel should have debug enabled
        """
        When I run Behat
        Then it should pass

    Scenario: Using configured environment
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

    Scenario: Using configured debug setting
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
