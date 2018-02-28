Feature: Not crashing Behat
    In order to use this extension
    As a Behat User
    I want to have Behat up and running after enabling this extension

    Scenario: Not crashing Behat
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        bootstrap: ~
        """
        And a file "app/AppKernel.php" containing:
        """
        <?php

        use Symfony\Component\HttpKernel\Kernel;
        use Symfony\Component\Config\Loader\LoaderInterface;

        class AppKernel extends Kernel
        {
            public function registerBundles() { return []; }
            public function registerContainerConfiguration(LoaderInterface $loader) {}
        }
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass

    Scenario: Not crashing Behat with CrossContainerExtension
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        bootstrap: ~

                FriendsOfBehat\CrossContainerExtension: ~
        """
        And a file "app/AppKernel.php" containing:
        """
        <?php

        use Symfony\Component\HttpKernel\Kernel;
        use Symfony\Component\Config\Loader\LoaderInterface;

        class AppKernel extends Kernel
        {
            public function registerBundles() { return []; }
            public function registerContainerConfiguration(LoaderInterface $loader) {}
        }
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass

    Scenario: This extension boot a Symfony4 kernel
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    env_file: .env_in_memory
                    kernel:
                        path: src/MyKernel.php
                        class: MyKernel
        """
        And a file ".env_in_memory" containing:
        """
        APP_ENV=dev
        """
        And a file "src/MyKernel.php" containing:
        """
        <?php

        use Symfony\Component\HttpKernel\Kernel;
        use Symfony\Component\Config\Loader\LoaderInterface;

        class MyKernel extends Kernel
        {
            public function registerBundles() { return []; }
            public function registerContainerConfiguration(LoaderInterface $loader) {}
        }
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass
