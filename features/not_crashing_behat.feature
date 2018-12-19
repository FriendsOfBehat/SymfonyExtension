Feature: Not crashing Behat
    In order to use this extension
    As a Behat User
    I want to have Behat up and running after enabling this extension

    Scenario: Successful boot the Symfony kernel with autoconfiguration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension: ~
        """
        And a file "app/autoload.php" containing:
        """
        <?php

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

    Scenario: Successful boot the Symfony kernel with explicit configuration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    kernel:
                        bootstrap: app/autoload.php
                        path: app/MyKernel.php
                        class: MyKernel
                        env: test
                        debug: true
        """
        And a file "app/autoload.php" containing:
        """
        <?php

        """
        And a file "app/MyKernel.php" containing:
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


    Scenario: Successful boot the Symfony 4 kernel with autoconfiguration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension: ~
        """
        And a file ".env" containing:
        """
        APP_ENV=dev
        """
        And a file "src/Kernel.php" containing:
        """
        <?php

        namespace App;

        use Symfony\Component\HttpKernel\Kernel as BaseKernel;
        use Symfony\Component\Config\Loader\LoaderInterface;

        class Kernel extends BaseKernel
        {
            public function registerBundles() { return []; }
            public function registerContainerConfiguration(LoaderInterface $loader) {}
        }
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass

    Scenario: Successful boot the Symfony 4 kernel with explicit configuration
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    env_file: ../config/.env_in_memory
                    kernel:
                        path: src/MyKernel.php
                        class: MyKernel
                        bootstrap: ~
        """
        And a file "../config/.env_in_memory" containing:
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

    Scenario: This extension used dist file by default
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension:
                    env_file: .env_in_memory
                    kernel:
                        bootstrap: ~
                        path: src/MyKernel.php
                        class: MyKernel
                        env: dev
                        debug: true
        """
        And a file ".env_in_memory.dist" containing:
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

    Scenario: Not crashing Behat with CrossContainerExtension
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\SymfonyExtension: ~
                FriendsOfBehat\CrossContainerExtension: ~
        """
        And a file "app/autoload.php" containing:
        """
        <?php

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
