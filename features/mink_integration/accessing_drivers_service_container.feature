Feature: Accessing driver's service container

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
                Given the counter service is zeroed
                When I visit the page "/hello-world"
                Then the counter service should return 1

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
            private $container;

            public function __construct(Mink $mink, ContainerInterface $container)
            {
                $this->mink = $mink;
                $this->container = $container;
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

            /** @Then the counter service should return :number */
            public function counterServiceShouldReturn(int $number): void
            {
                assert($number === $this->getCounterService()->get());
            }

            private function getCounterService(): Counter
            {
                return $this->container
                    ->get('behat.service_container')
                    ->get('fob_symfony.driver_kernel')
                    ->getContainer()
                    ->get('test.service_container')
                    ->get('App\Counter')
                ;
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
                        - '@service_container'
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
