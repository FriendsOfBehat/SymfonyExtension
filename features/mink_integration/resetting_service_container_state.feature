Feature: Resetting the driver's service container in the right places

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
        And a YAML services file containing:
        """
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\Tests\SomeContext: ~
        """
        And a context file "tests/SomeContext.php" containing:
        """
        <?php

        namespace App\Tests;

        use App\Counter;
        use Behat\Behat\Context\Context;
        use Behat\Mink\Mink;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        final class SomeContext implements Context {
            private $mink;
            private $driverContainer;

            public function __construct(Mink $mink, ContainerInterface $driverContainer)
            {
                $this->mink = $mink;
                $this->driverContainer = $driverContainer;
            }

            /** @Given the counter service is zeroed */
            public function counterServiceIsZeroed(): void
            {
                assert(0 === $this->getCounterService()->get());
            }

            /** @When I visit the page :page */
            public function visitPage(string $page): void
            {
                $this->mink->getSession()->visit($page);
            }

            /** @When I increment the counter */
            public function incrementCounter(): void
            {
                $this->getCounterService()->increase();
            }

            /** @Then the counter service should return :number */
            public function counterServiceShouldReturn(int $number): void
            {
                assert($number === $this->getCounterService()->get());
            }

            /** @Then I should see :content on the page */
            public function shouldSeeContentOnPage(string $content): void
            {
                assert(false !== strpos($this->mink->getSession()->getPage()->getContent(), $content));
            }

            private function getCounterService(): Counter
            {
                return $this->driverContainer->get('App\Counter');
            }
        }
        """

    Scenario: Driver's service container is reset between scenarios
        # Regression testing https://github.com/FriendsOfBehat/SymfonyExtension/issues/149
        Given a feature file containing:
        """
        Feature:
            Scenario: First pass
                Given the counter service is zeroed
                When I increment the counter
                Then the counter service should return 1
            Scenario: Second pass
                Given the counter service is zeroed
        """
        When I run Behat
        Then it should pass

    Scenario: Driver's service container is reset between requests
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                When I visit the page "/hello-world"
                Then I should see "The counter value is 1" on the page
                When I visit the page "/hello-world"
                Then I should see "The counter value is 1" on the page
        """
        When I run Behat
        Then it should pass

    Scenario: Driver's service container can be prepared before a request is made
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                And I increment the counter
                When I visit the page "/hello-world"
                Then I should see "The counter value is 2" on the page
        """
        When I run Behat
        Then it should pass

    Scenario: Driver's service container is not reset before a request is made, even when another scenario made a request before
        # Regression testing https://github.com/FriendsOfBehat/SymfonyExtension/issues/149
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                And I increment the counter
                When I visit the page "/hello-world"
                Then I should see "The counter value is 2" on the page
            Scenario:
                Given the counter service is zeroed
                And I increment the counter
                When I visit the page "/hello-world"
                Then I should see "The counter value is 2" on the page
        """
        When I run Behat
        Then it should pass

    Scenario: Driver's service container can be inspected after a request has been made
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                When I visit the page "/hello-world"
                Then the counter service should return 1
        """
        When I run Behat
        Then it should pass

    Scenario: When multiple requests are made, the driver's service container is reset, but we can inspect "in between" states
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                When I visit the page "/hello-world"
                Then the counter service should return 1
                # This will reset the driver's container, so we will see "1" again
                When I visit the page "/hello-world"
                Then the counter service should return 1
        """
        When I run Behat
        Then it should pass

    Scenario: Driver's service container can not reasonably be modified between requests
        # This is not really a feature, but rather documenting current behavior (judge yourself).
        # One way around it might be to (how?) disable the reboot feature on the KernelBrowser and
        # take responsibility for resetting the driver's container yourself.
        Given a feature file containing:
        """
        Feature:
            Scenario:
                Given the counter service is zeroed
                And I increment the counter
                When I visit the page "/hello-world"
                Then I should see "The counter value is 2" on the page
                And the counter service should return 2
                # Now a second request is made, which will reset the container, but leaves us
                # with no easy way of pre-setting the container once again:
                When I increment the counter
                # ... you might expect "3"
                And I visit the page "/hello-world"
                # ... now you might expect "4". But, in fact, the reset happened just before the request.
                Then I should see "The counter value is 1" on the page
                And the counter service should return 1
        """
        When I run Behat
        Then it should pass
