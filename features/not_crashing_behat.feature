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
