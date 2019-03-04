Feature: Accessing a context in another context

    Scenario: Accessing a context in another context
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
                        - App\Tests\AnotherContext
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
            public function someMethod(): void {}
        }
        """
        And a context file "tests/AnotherContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Behat\Hook\Scope\BeforeScenarioScope;

        final class AnotherContext implements Context {
            /** @var SomeContext */
            private $someContext;

            /** @BeforeScenario */
            public function gatherContexts(BeforeScenarioScope $scope)
            {
                $environment = $scope->getEnvironment();

                $this->someContext = $environment->getContext('App\Tests\SomeContext');
            }

            /** @Then it should pass */
            public function itShouldPass(): void
            {
                $this->someContext->someMethod();
            }
        }
        """
        And a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true

                App\Tests\AnotherContext:
                    public: true
            """
        When I run Behat
        Then it should pass
