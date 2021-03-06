@paying_with_billogram_for_order
Feature: Paying with Billogram during checkout
    In order to buy products
    As a Customer
    I want to be able to pay with Billogram

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@debricked.com" identified by "password123"
        And the store has a payment method "Billogram" with a code "billogram" and Billogram payment gateway
        And the store has a product "PHP T-Shirt" priced at "€19.99"
        And the store ships everywhere for free
        And I am logged in as "john@debricked.com"

    @ui
    Scenario: Successful payment
        Given I added product "PHP T-Shirt" to the cart
        And I have proceeded selecting "Billogram" payment method
        When I confirm my order with Billogram payment
        Then I should be notified that my payment has been completed
