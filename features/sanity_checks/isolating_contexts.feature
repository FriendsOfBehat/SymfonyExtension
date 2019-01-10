Feature: Isolating contexts

    Scenario: Keeping contexts isolated between scenarios
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
            Scenario: First scenario
                Then the property should be "shit happens"

            Scenario: Second scenario
                When I change the property to "shit does not happen"
                Then the property should be "shit does not happen"

            Scenario: Third scenario
                Then the property should be "shit happens"
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Psr\Container\ContainerInterface;

        final class SomeContext implements Context {
            private $property = 'shit happens';

            public function __construct(?ContainerInterface $container = null) { $this->container = $container; }

            /** @When I change the property to :value */
            public function changeProperty(string $value): void { $this->property = $value; }

            /** @Then the property should be :expected*/
            public function propertyShouldBe(string $expected): void { assert($this->property === $expected); }
        }
        """
        And a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true
            """
        When I run Behat
        Then it should pass
