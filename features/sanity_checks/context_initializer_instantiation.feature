Feature: instantiation of a context initializer
  I want container to only keep 1 instance of context initializer
  (if it's a shared service) so it can work correctly if it's stateful

  Scenario:
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
            test.initializer:
                class: App\Tests\CustomContextInitializer
                tags: ["context.initializer", "event_dispatcher.subscriber"]
        """
    And a Behat service implementation file "tests/CustomContextInitializer.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Behat\Context\Initializer\ContextInitializer;
        use Symfony\Component\EventDispatcher\EventSubscriberInterface;

        final class CustomContextInitializer implements ContextInitializer, EventSubscriberInterface
        {
            public static $counter = 0;

            public function __construct()
            {
                ++self::$counter;
            }

            public function initializeContext(Context $context): void
            {
                $context->makeItPass(true);
            }

            public static function getSubscribedEvents()
            {
                return [];
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
                $actualInitializersCount = CustomContextInitializer::$counter;
                assert(1 === $actualInitializersCount, "$actualInitializersCount initializers were created");
                assert($this->shouldPass === true);
            }
        }
        """
    When I run Behat
    Then it should pass
