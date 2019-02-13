Feature: Context constructor dependency injection compatibility

    Scenario: Using context constructor dependency injection
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext:
                            - "@App\\Foo"

                    services:
                        App\Foo: ~
        """
        And a class file "src/Foo.php" containing:
        """
        <?php

        namespace App;

        final class Foo
        {
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

        use App\Foo;
        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            public function __construct(Foo $foo)
            {
                $this->foo = $foo;
            }

            /** @Then it should pass */
            public function itShouldPass(): void
            {
                assert($this->foo instanceof Foo);
            }
        }
        """
        When I run Behat
        Then it should pass
