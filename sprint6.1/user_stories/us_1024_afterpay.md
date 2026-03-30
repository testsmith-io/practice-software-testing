# US_1024 Afterpay

## User story

As a user located in the US, \
I want to be able to use Afterpay as a payment method.

## Acceptance criteria

Current task requires fixing [this bug](#bug-description)  \
if the app is to be run in edge explorer browser.

Check the billing address of the customer. \
If the user is located in the US make Afterpay payment option available \
in selection of payments.
If defining location is disabled, then no Afterpay option is possible.

## Bug description

[Environment](https://practicesoftwaretesting.com/checkout)

### Steps to reproduce
- Click URL
- Create a user
- Sign in as the new user
- Add item to basket
- Proceed to filling billing address

### Expected result:
After filling in billing address a green button next is \
in the right corner of the form and it's possible to click on it and \
proceed to payment option selection.

### Actual result:
Not possible to proceed further to payment. \
The next button is with no text and is not working.