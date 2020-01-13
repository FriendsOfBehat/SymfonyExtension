Feature: FriendsOfBehat/PageObjectExtension integration

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
                When I visit the homepage
                Then I should see "Hello world!" on the page
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use Behat\Behat\Context\Context;

        final class SomeContext implements Context {
            private $homepage;

            public function __construct(Homepage $homepage)
            {
                $this->homepage = $homepage;
            }

            /** @When I visit the homepage */
            public function visitPage(): void
            {
                $this->homepage->open();
            }

            /** @Then I should see :content on the page */
            public function shouldSeeContentOnPage(string $content): void
            {
                assert(false !== strpos($this->homepage->getContent(), $content));
            }
        }
        """
        And a page file "tests/Homepage.php" containing:
        """
        <?php

        namespace App\Tests;

        use FriendsOfBehat\PageObjectExtension\Page\Page;

        final class Homepage extends Page
        {
            public function getContent(): string
            {
                return $this->getDocument()->getContent();
            }

            protected function getUrl(array $urlParameters = []): string
            {
                return 'http://localhost:8080/hello-world';
            }
        }
        """

    Scenario: Injecting page into the context
        Given a YAML services file containing:
            """
            services:
                App\Tests\Homepage:
                    arguments:
                        - '@behat.mink.default_session'
                        - '@behat.mink.parameters'

                App\Tests\SomeContext:
                    public: true
                    arguments:
                        - '@App\Tests\Homepage'
            """
        When I run Behat
        Then it should pass

    Scenario: Autowiring and autoconfiguring page
        Given a YAML services file containing:
            """
            services:
                _defaults:
                    autowire: true
                    autoconfigure: true

                App\Tests\Homepage: ~

                App\Tests\SomeContext: ~
            """
        When I run Behat
        Then it should pass
