# US3411 - New step (login) in checkout workflow
Background: Until now, when the user is not logged-in and runs the checkout workflow, 
he/she can not finish the workflow. This is very annoying for the user.

## User Story: Login during checkout workflow
As a user who is not logged in
I want the possibility to perform a login during the checkout workflow
to be able to finish the workflow.

## Acceptance Criteria:

### UC1: User is not logged-in
Given the user is not logged-in 
And   on the checkout-UI
When  the user clicks "Proceed to Checkout"
Then  the login dialogue will be displayed
And   after a successful login the first checkout step will be displayed.

### UC2: User is logged-in
Given the user is logged-in 
And   on the checkout-UI
When  the user clicks "Proceed to Checkout"
Then  the first checkout step will be displayed.

# Alternatives:

# Errors: