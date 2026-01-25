Feature: Configuring kernel reboot between scenarios

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

        use App\Counter;
        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private static $counter;

            public function __construct(Counter $counter)
            {
                self::$counter = $counter;
            }

            /** @Given I increment the counter */
            public function incrementCounter(): void
            {
                self::$counter->increase();
            }

            /** @Then the counter should be :value */
            public function counterShouldBe(int $value): void
            {
                assert(self::$counter->get() === $value, sprintf('Expected %d, got %d', $value, self::$counter->get()));
            }
        }
        """
        And a YAML services file containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
                arguments:
                    - '@App\Counter'

            App\Counter:
                public: true
        """

    Scenario: Kernel reboots between scenarios by default (state is isolated)
        Given a feature file containing:
        """
        Feature:
            Scenario: First scenario increments counter
                Given I increment the counter
                Then the counter should be 1

            Scenario: Second scenario starts fresh
                Then the counter should be 0
        """
        When I run Behat
        Then it should pass

    Scenario: Kernel reboot can be disabled (state persists between scenarios)
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        reboot: false
        """
        And a feature file containing:
        """
        Feature:
            Scenario: First scenario increments counter
                Given I increment the counter
                Then the counter should be 1

            Scenario: Second scenario sees previous state
                Given I increment the counter
                Then the counter should be 2
        """
        When I run Behat
        Then it should pass

    Scenario: Kernel reboot explicitly enabled works as default
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        reboot: true
        """
        And a feature file containing:
        """
        Feature:
            Scenario: First scenario increments counter
                Given I increment the counter
                Then the counter should be 1

            Scenario: Second scenario starts fresh
                Then the counter should be 0
        """
        When I run Behat
        Then it should pass
