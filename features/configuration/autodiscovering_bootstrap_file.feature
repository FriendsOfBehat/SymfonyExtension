Feature: Autodiscovering bootstrap file

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

    Scenario: Autodiscovering bootstrap file in Symfony 4 directory structure application
        Given a boostrap file "config/bootstrap.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        When I run Behat
        Then it should pass

    Scenario: Autodiscovering bootstrap file in Symfony 3 directory structure application
        Given a boostrap file "app/autoload.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        When I run Behat
        Then it should pass

    Scenario: Failing to autodiscover the bootstrap file
        Given a boostrap file "config/bootstrap.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        And a boostrap file "app/autoload.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        When I run Behat
        Then it should fail with "Could not autodiscover the bootstrap file"

    Scenario: Not loading autodiscovered bootstrap file if explicitly disabled
        Given a boostrap file "config/bootstrap.php" containing:
        """
        <?php

        putenv("CUSTOM_VARIABLE=lol2");
        $_SERVER['CUSTOM_VARIABLE'] = $_ENV['CUSTOM_VARIABLE'] = 'lol2';
        """
        And a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    bootstrap: false
        """
        When I run Behat
        Then it should fail with "Symfony\Component\DependencyInjection\Exception\EnvNotFoundException"
