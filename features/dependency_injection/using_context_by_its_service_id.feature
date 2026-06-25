Feature: Using context by its service ID

    Background:
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withSuite(
                        (new \Behat\Config\Suite('default'))
                            ->withContexts('behat.context.some_context')
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
            public function shouldPass(): void
            {
            }
        }
        """

    Scenario: Using context by its service ID (context explicitly set as public)
        And a YAML services file containing:
            """
            services:
                behat.context.some_context:
                    class: App\Tests\SomeContext
                    public: true
                    arguments:
                        - "@service_container"
            """
        When I run Behat
        Then it should pass

    Scenario: Using context by its service ID (autoconfigured context)
        And a YAML services file containing:
            """
            services:
                _defaults:
                    autoconfigure: true

                behat.context.some_context:
                    class: App\Tests\SomeContext
                    arguments:
                        - "@service_container"
            """
        When I run Behat
        Then it should pass
