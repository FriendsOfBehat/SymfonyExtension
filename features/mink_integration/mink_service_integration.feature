Feature: Mink service integration

    Background:
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

            # Doubling the scenario to account for some weird error connected to Mink's session
            Scenario:
                When I visit the page "/hello-world"
                Then I should see "Hello world!" on the page
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;
        use Behat\Mink\Mink;
        use Psr\Container\ContainerInterface;

        final class SomeContext implements Context {
            private $mink;

            public function __construct(Mink $mink)
            {
                $this->mink = $mink;
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
        }
        """

    Scenario: Injecting Mink serivce
        Given a YAML services file containing:
            """
            services:
                App\Tests\SomeContext:
                    public: true
                    arguments:
                        - '@behat.mink'
            """
        When I run Behat
        Then it should pass

    Scenario: Autowiring and autoconfiguring Mink service
        Given a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true
                    autoconfigure: true

                App\Tests\SomeContext: ~
            """
        When I run Behat
        Then it should pass
