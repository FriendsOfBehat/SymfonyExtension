Feature: Mink integration with context initializer

    Scenario: Passing Mink instance and parameters through context initializer
        Given a working Symfony application with SymfonyExtension configured
        And a Behat configuration containing:
        """
        default:
            extensions:
                Behat\MinkExtension:
                    base_url: "http://localhost:8080/"
                    default_session: symfony
                    sessions:
                        symfony:
                            symfony: ~
            suites:
                default:
                    contexts:
                        - App\Tests\SomeContext
        """
        And a feature file containing:
        """
        Feature:
            Scenario:
                When I visit the page "/hello-world"
                Then I should see "Hello world!" on the page
                And the base url from Mink parameters should be "http://localhost:8080/"

            # Doubling the scenario to account for some weird error connected to Mink's session
            Scenario:
                When I visit the page "/hello-world"
                Then I should see "Hello world!" on the page
                And the base url from Mink parameters should be "http://localhost:8080/"
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Mink\Mink;
        use Behat\MinkExtension\Context\MinkAwareContext;

        final class SomeContext implements MinkAwareContext {
            private $mink;
            private $parameters;

            public function setMink(Mink $mink): void
            {
                $this->mink = $mink;
            }

            public function setMinkParameters(array $minkParameters): void
            {
                $this->parameters = $minkParameters;
            }

            /** @When I visit the page :page */
            public function visitPage(string $page): void
            {
                $this->mink->getSession()->visit($page);
            }

            /** @Then I should see :content on the page */
            public function shouldSeeContentOnPage(string $content): void
            {
                assert(false !== strpos($this->mink->getSession()->getPage()->getContent(), $content));
            }

            /** @Then the base url from Mink parameters should be :expected */
            public function baseUrlShouldBe(string $expected): void
            {
                assert(isset($this->parameters['base_url']));
                assert($this->parameters['base_url'] === $expected);
            }
        }
        """
        When I run Behat
        Then it should pass
