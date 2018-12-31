Feature: Running bare Behat scenarios

    Scenario: Running Behat with SymfonyExtension
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        path: app/AppKernel.php
                        class: AppKernel

                Behat\MinkExtension:
                    base_url: "http://localhost:8080/"
                    default_session: symfony
                    sessions:
                        symfony:
                            symfony: ~
        """
        And a file "app/AppKernel.php" containing:
        """
        <?php

        use Symfony\Component\HttpKernel\Kernel;
        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\Config\Loader\LoaderInterface;

        class AppKernel extends Kernel
        {
            public function registerBundles()
            {
                return [
                    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new \FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle(),
                ];
            }

            public function registerContainerConfiguration(LoaderInterface $loader)
            {
                $loader->load(function (ContainerBuilder $container): void {
                    $container->loadFromExtension('framework', [
                        'test' => $this->getEnvironment() === 'test',
                        'secret' => 'Pigeon',
                    ]);
                });
            }
        }
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass
