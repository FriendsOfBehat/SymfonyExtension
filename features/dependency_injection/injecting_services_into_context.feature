Feature: Injecting services into context

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
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then the passed service should be an instance of "\Psr\Container\ContainerInterface"
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $service;

            public function __construct($service = null) { $this->service = $service; }

            /** @Then the passed service should be an instance of :expected */
            public function serviceShouldBe(string $expected): void
            {
                assert(is_object($this->service));
                assert($this->service instanceof $expected);
            }
        }
        """

    Scenario: Injecting a service into a context explicitly set as public
        Given a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true
                    arguments:
                        - "@service_container"
            """
        When I run Behat
        Then it should pass

    Scenario: Injecting a service into an autoconfigured context
        Given a YAML services file containing:
            """
            services:
                _defaults:
                    autoconfigure: true

                App\Tests\SomeContext:
                    arguments:
                        - "@service_container"
            """
        When I run Behat
        Then it should pass
