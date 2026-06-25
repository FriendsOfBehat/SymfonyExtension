Feature: Context constructor dependency injection compatibility

    Scenario: Using context constructor dependency injection
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withSuite(
                        (new \Behat\Config\Suite(
                            'default',
                            [
                                'services' => [
                                    'App\Foo' => ['class' => 'App\Foo']
                                ]
                            ]
                        )
                    )
                    ->addContext('App\Tests\SomeContext', ['@App\\Foo'])
                )
            );
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
        use Behat\Step\Then;

        final class SomeContext implements Context {
            public function __construct(Foo $foo)
            {
                $this->foo = $foo;
            }

            #[Then('it should pass')]
            public function itShouldPass(): void
            {
                assert($this->foo instanceof Foo);
            }
        }
        """
        When I run Behat
        Then it should pass
