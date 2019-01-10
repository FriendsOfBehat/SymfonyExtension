Feature: Injecting parameters into context

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
                Then the passed parameter should be "test"
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $parameter;

            public function __construct(?string $parameter = null) { $this->parameter = $parameter; }

            /** @Then the passed parameter should be :expected */
            public function parameterShouldBe(string $expected): void { assert($this->parameter === $expected); }
        }
        """

    Scenario: Injecting a parameter into a context explicitly set as public
        Given a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true
                    arguments:
                        - "%kernel.environment%"
            """
        When I run Behat
        Then it should pass

    Scenario: Injecting a parameter into an autoconfigured context
        Given a YAML services file containing:
            """
            services:
                _defaults:
                    autoconfigure: true

                App\Tests\SomeContext:
                    arguments:
                        - "%kernel.environment%"
            """
        When I run Behat
        Then it should pass
