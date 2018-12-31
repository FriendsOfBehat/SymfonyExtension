Feature: Running bare Behat scenarios

    Scenario: Running Behat with SymfonyExtension
        Given a Behat configuration with the minimal working configuration for SymfonyExtension
        And a Behat configuration with the minimal working configuration for MinkExtension
        And an application kernel with the minimal working configuration for SymfonyExtension
        And a feature file with passing scenario
        When I run Behat
        Then it should pass
