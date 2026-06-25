Feature: Switching Mink sessions

    Scenario: Switching Mink sessions
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        <?php

        return (new \Behat\Config\Config())
            ->withProfile(
                (new \Behat\Config\Profile('default'))
                    ->withExtension(
                      new \Behat\Config\Extension(
                        \Behat\MinkExtension\ServiceContainer\MinkExtension::class,
                        [
                            'base_url' => 'http://localhost:8080/',
                            'default_session' => 'symfony',
                            'javascript_session' => 'selenium2',
                            'sessions' => [
                                ['name' => 'symfony', 'symfony' => []],
                                ['name' =>'selenium2', 'selenium2' => []],
                            ]
                        ]
                      )
                    )
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
                Then I should use Mink session with "FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver" as a driver

            @javascript
            Scenario:
                Then I should use Mink session with "Behat\Mink\Driver\Selenium2Driver" as a driver
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Mink\Session;
        use Behat\Step\Then;

        final class SomeContext implements Context {
            private $session;

            public function __construct(Session $session)
            {
                $this->session = $session;
            }

            #[Then('I should use Mink session with :driver as a driver')]
            public function shouldUseDriver(string $driverClass): void
            {
                assert($this->session->getDriver() instanceof $driverClass);
            }
        }
        """
        And a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true
                    arguments:
                        - '@behat.mink.default_session'
            """
        When I run Behat
        Then it should pass
