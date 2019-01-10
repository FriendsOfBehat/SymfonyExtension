Feature: Loading configured bootstrap file

    Scenario: Loading configured bootstrap file
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    bootstrap: custom/bootstrap.php

            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
        """
        And a boostrap file "custom/bootstrap.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $parameter;

            public function __construct(?string $parameter = null) { $this->parameter = $parameter; }

            /** @Then the passed parameter should be :expected */
            public function parameterShouldBe(string $expected): void { assert($this->parameter === $expected); }
        }
        """
        And a YAML services file containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
                arguments:
                    - "%env(CUSTOM_VARIABLE)%"
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then the passed parameter should be "lol2"
        """
        When I run Behat
        Then it should pass
