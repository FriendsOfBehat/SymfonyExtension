Feature: Loading configured bootstrap file

    Scenario: Loading configured bootstrap file
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withExtension(
                      new \Behat\Config\Extension(
                        \FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension::class,
                        ['bootstrap' => 'custom/bootstrap.php']
                      )
                    )
                    ->withSuite(
                        (new \Behat\Config\Suite('default'))
                            ->withContexts('App\Tests\SomeContext')
                    )
            );
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
        use Behat\Step\Then;

        final class SomeContext implements Context {
            private $parameter;

            public function __construct(?string $parameter = null) { $this->parameter = $parameter; }

            #[Then('the passed parameter should be :expected')]
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

    Scenario: Loading configured bootstrap file with parameter resolution
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withExtension(
                      new \Behat\Config\Extension(
                        \FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension::class,
                        ['bootstrap' => '%paths.base%/custom/bootstrap.php']
                      )
                    )
                    ->withSuite(
                        (new \Behat\Config\Suite('default'))
                            ->withContexts('App\Tests\SomeContext')
                    )
            );
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
        use Behat\Step\Then;

        final class SomeContext implements Context {
            private $parameter;

            public function __construct(?string $parameter = null) { $this->parameter = $parameter; }

            #[Then('the passed parameter should be :expected')]
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
