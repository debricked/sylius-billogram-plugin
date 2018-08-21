@managing_billogram_payment_method
Feature: Adding a new billogram payment method
    In order to pay for orders in different ways
    As an Administrator
    I want to add a new payment method to the registry

    Background:
        Given the store operates on a channel named "Web-SEK" in "SEK" currency
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new billogram payment method
        Given I want to create a new Billogram payment method
        When I name it "Billogram" in "English (United States)"
        And I specify its code as "billogram_test"
        And I fill the username with "a-valid-username" and the API key with "db79a3916d936a73bdff382d565ca3e3" and the API url with "https://billogram.com/api/v2"
        And make it available in channel "Web-SEK"
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "Billogram" should appear in the registry
