# US3207 - New payment method China Pay
Background: Toolshop wants to enter the China market and must offer additional payment method.

## User Story: Enable China Pay as payment method

As a shop owner,
I want that the user can pay with China Pay
to offer an additional payment method.

## Acceptance Criteria:

### UC1: Enable China Pay
Given the admin is on the setup payment UI
When  the admin set "China Pay" = enabled
Then  the user can select "China Pay" from the payment list of value.

### UC2: Pay via China Pay
Given the user is on the checkout UI 
When  the user opens the payment list of values 
And   the admin has enabled "China Pay" as payment method
And   the location of the user is within "China"
Then  "China Pay" is displayed in the list and can be selected.

### UC3: Process payment via China Pay
Given the user is on the checkout UI
When  the user select "China Pay" 
And   the user press the "Pay"-Button
Then  the payment will be processed via payment gateway "XBO-China-Pay-SD0923".

# Alternatives:

# Errors: