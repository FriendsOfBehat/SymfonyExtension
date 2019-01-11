Feature: Loading configured application kernel

    Background:
        Given a standard Symfony autoloader configured
        And a feature file containing:
        """
        Feature:
            Scenario:
                Then the passed service should be an instance of "\Psr\Container\ContainerInterface"
        """
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
            private $service;

            public function __construct($service = null) { $this->service = $service; }

            /** @Then the passed service should be an instance of :expected */
            public function serviceShouldBe(string $expected): void
            {
                assert(is_object($this->service));
                assert($this->service instanceof $expected);
            }
        }
        """
        And a services file "config/services.yaml" containing:
        """
        services:
            App\Tests\SomeContext:
                public: true
                arguments:
                    - "@service_container"
        """

    Scenario: Loading kernel by its classname
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        class: App\Custom\Kernel
        """
        And a kernel file "src/Custom/Kernel.php" containing:
        """
        <?php

        namespace App\Custom;

        use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
        use Symfony\Component\Config\Loader\LoaderInterface;
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\HttpKernel\Kernel as HttpKernel;
        use Symfony\Component\Routing\RouteCollectionBuilder;

        class Kernel extends HttpKernel
        {
            use MicroKernelTrait;

            public function registerBundles(): iterable
            {
                return [
                    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new \FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle(),
                ];
            }

            protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
            {
                $container->loadFromExtension('framework', [
                    'test' => true,
                    'secret' => 'Pigeon',
                ]);

                $loader->load(__DIR__ . '/../../config/services.yaml');
            }

            protected function configureRoutes(RouteCollectionBuilder $routes): void {}
        }
        """
        When I run Behat
        Then it should pass

    Scenario: Loading kernel from custom path
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        path: app/Nested/Kernel.php
                        class: AppKernel
        """
        And a kernel file "app/Nested/Kernel.php" containing:
        """
        <?php

        use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
        use Symfony\Component\Config\Loader\LoaderInterface;
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\HttpKernel\Kernel as HttpKernel;
        use Symfony\Component\Routing\RouteCollectionBuilder;

        class AppKernel extends HttpKernel
        {
            use MicroKernelTrait;

            public function registerBundles(): iterable
            {
                return [
                    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new \FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle(),
                ];
            }

            protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
            {
                $container->loadFromExtension('framework', [
                    'test' => true,
                    'secret' => 'Pigeon',
                ]);

                $loader->load(__DIR__ . '/../../config/services.yaml');
            }

            protected function configureRoutes(RouteCollectionBuilder $routes): void {}
        }
        """
        When I run Behat
        Then it should pass
