Feature: Class resolvers compatibility

    Scenario: Using class resolvers while handling context environment
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - "tests/class_resolver.yml"

            suites:
                default:
                    contexts:
                        - class:resolved:context
        """
        And a Behat services definition file "tests/class_resolver.yml" containing:
        """
        services:
            App\Tests\CustomClassResolver:
                tags: ["context.class_resolver"]
        """
        And a Behat service implementation file "tests/CustomClassResolver.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\ContextClass\ClassResolver;

        final class CustomClassResolver implements ClassResolver
        {
            public function supportsClass($contextClass): bool
            {
                return $contextClass === 'class:resolved:context';
            }

            public function resolveClass($contextClass): string
            {
                return 'App\Tests\SomeContext';
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
            /** @Then it should pass */
            public function itShouldPass(): void {}
        }
        """
        When I run Behat
        Then it should pass
