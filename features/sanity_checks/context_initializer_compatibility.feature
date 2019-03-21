Feature: Context initializer compatibility

    Background:
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - "tests/context_initializer.yml"

            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
        """
        And a Behat services definition file "tests/context_initializer.yml" containing:
        """
        services:
            App\Tests\CustomContextInitializer:
                tags: ["context.initializer"]
        """
        And a Behat service implementation file "tests/CustomContextInitializer.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Behat\Context\Initializer\ContextInitializer;

        final class CustomContextInitializer implements ContextInitializer
        {
            public function initializeContext(Context $context): void
            {
                $context->makeItPass(true);
            }
        }
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then it should pass
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $shouldPass = false;

            public function makeItPass(bool $shouldPass)
            {
                $this->shouldPass = $shouldPass;
            }

            /** @Then it should pass */
            public function itShouldPass(): void
            {
                assert($this->shouldPass === true);
            }
        }
        """

    Scenario: Using context initializers while handling context environment for vanilla contexts
        When I run Behat
        Then it should pass

    Scenario: Using context initializers while handling context environment for contexts as a service
        Given a YAML services file containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
        """
        When I run Behat
        Then it should pass
