Feature: Injecting parameters into context

    Background:
        Given a context file "features/bootstrap/FeatureContext.php" containing:
            """
            <?php

            use Behat\Behat\Context\Context;
            use Symfony\Component\DependencyInjection\ContainerInterface;

            class FeatureContext implements Context
            {
                private $parameter;

                public function __construct(string $parameter)
                {
                    $this->parameter = $parameter;
                }

                /**
                 * @Then the parameter should be injected into the context
                 */
                public function behatContainerShouldBeInjectedToTheContext()
                {
                    if (null === $this->parameter || empty($this->parameter)) {
                        throw new \DomainException('Required parameter was not injected!');
                    }
                }
            }
            """
        And an application kernel injecting a parameter into the FeatureContext class
        And a Behat configuration with the minimal working configuration for SymfonyExtension
        And a Behat configuration with the minimal working configuration for MinkExtension


    Scenario: Injecting parameters into context with SymfonyExtension
        Given a feature file "features/injecting_parameter_into_context.feature" containing:
            """
            Feature: Injecting parameter into the context
                Scenario:
                    Then the parameter should be injected into the context
            """
        When I run Behat
        Then it should pass
