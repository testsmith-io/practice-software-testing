# US9000 - Display delivery costs in cart overview & Invoice PDF

Background: Customers currently see item totals and discounts in the cart, but no delivery fee. Toolshop wants shoppers to see a flat delivery charge before proceeding through checkout, so the cart total reflects the full expected cost of the order.

## User Story: Display delivery costs in cart overview

As a customer shopping in the web shop,
I want to see delivery costs included in my cart total on the checkout cart page,
so that I know the full amount I will pay before continuing checkout.

## Acceptance Criteria:

### UC1: Delivery cost shown for non-empty cart

Given the customer is on the checkout cart page (step 1)
And   the cart contains at least one item
When  the cart totals are displayed
Then  a "Delivery costs" row is shown with a flat fee of 7.90
And   the fee uses the same currency symbol as the cart items (e.g. $7.90 or Kc 7.90)
And   the row is labelled via the translation key `pages.checkout.cart.delivery-cost`

### UC2: Delivery cost included in cart total

Given the customer is on the checkout cart page
And   the cart contains at least one item
When  the cart totals are calculated
Then  delivery costs are added after all cart-level and eco discounts
And   delivery costs are not reduced by any discount
And   the grand total (`data-test="cart-total"`) equals: line items − discounts + 7.90

### UC3: No delivery cost for empty cart

Given the customer is on the checkout cart page
And   the cart contains no items
When  the cart totals are displayed
Then  no delivery costs row is shown
And   no delivery fee is added to the total

### UC4: Delivery cost updates with cart changes

Given the customer is on the checkout cart page
And   the cart contains at least one item
When  the customer changes item quantities or removes items
Then  the delivery costs row and cart total update accordingly
And   delivery costs remain 7.90 as long as at least one item remains in the cart

### UC5: Delivery cost visible only on cart step

Given the customer proceeds through checkout  
When  the customer views the address, payment, or order confirmation steps  
Then  delivery costs are not shown on those steps

## Out of scope

- API, database, or backend invoice changes
- Payment step, order confirmation, and PDF invoice updates
- Address-step delivery method selector (Standard / Zasilkovna) — unrelated to cart fee
- Changes outside `practice-software-testing/sprint5-holtesting/`

## Example calculation

Cart with one item at $100, 15% cart discount, no eco discount:


| Row            | Amount     |
| -------------- | ---------- |
| Subtotal       | $100.00    |
| Discount (15%) | − $15.00   |
| Delivery costs | $7.90      |
| **Total**      | **$92.90** |


# Alternatives:

# Errors:

