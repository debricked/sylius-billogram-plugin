@managing_billogram_payment_method
Feature: Billogram payment method validation
    In order to avoid making mistakes when managing a payment method
    As an Administrator
    I want to be prevented from adding it without specifying required fields

    Background:
        Given the store operates on a channel named "Web-SEK" in "SEK" currency
        And the store has a payment method "Offline" with a code "offline"
        And I am logged in as an administrator

    @ui
    Scenario: Trying to add a new billogram payment method without specifying required configuration
        Given I want to create a new Billogram payment method
        When I name it "Billogram" in "English (United States)"
        And I add it
        Then I should be notified that "API key,Username,API url" fields cannot be blank

    @ui
    Scenario: Trying to add a new billogram payment method without the correct API key
        Given I want to create a new Billogram payment method
        When I name it "Billogram" in "English (United States)"
        And I fill the username with "a-valid-username" and the API key with "kYuKoGGJRG3Syaj1z9DXoUrxUsYD6MP" and the API url with "https://billogram.com/api/v2"
        And I add it
        Then I should be notified that "Invalid API key. An API key can only contain hexadecimal characters."
        And I should be notified that "API key must be at least 32 characters long."
