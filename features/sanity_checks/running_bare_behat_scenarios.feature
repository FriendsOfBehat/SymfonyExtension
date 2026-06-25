Feature: Running bare Behat scenarios

    Scenario: Running Behat with SymfonyExtension
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withSuite(
                        (new \Behat\Config\Suite('default'))
                            ->withContexts('App\Tests\SomeContext')
                    )
            );
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
        use Behat\Step\Then;

        final class SomeContext implements Context {
            #[Then('it should pass')]
            public function itShouldPass(): void {}
        }
        """
        When I run Behat
        Then it should pass
