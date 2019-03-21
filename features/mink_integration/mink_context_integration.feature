Feature: Mink context integration

    Background:
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            extensions:
                Behat\MinkExtension:
                    base_url: "http://localhost:8080/"
                    default_session: symfony
                    sessions:
                        symfony:
                            symfony: ~

            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then I should have Mink injected
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Mink\Mink;
        use Behat\Mink\Session;
        use Behat\MinkExtension\Context\MinkContext;

        final class SomeContext extends MinkContext implements Context {
            /** @Then I should have Mink injected */
            public function shouldHaveMinkInjected(): void
            {
                assert($this->getMink() instanceof Mink);
            }
        }
        """

    Scenario: Mink context integration with vanilla context
        When I run Behat
        Then it should pass

    Scenario: Mink context integration with context as a service
        Given a YAML services file containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
        """
        When I run Behat
        Then it should pass
