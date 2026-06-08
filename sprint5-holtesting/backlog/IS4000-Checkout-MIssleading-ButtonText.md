# IS4000 - Checkout payment step shows misleading button text after successful payment validation

**Type:** Bug  
**Area:** Checkout — payment step (step 3)  
**Component:** `UI/src/app/checkout/payment/`

## Summary

On the last checkout step, after the customer selects a payment method and clicks **Confirm**, the application validates the payment and displays the success message **"Payment was successful"**. The action button nevertheless keeps the label **Confirm**, which does not reflect what happens next and misleads the customer about whether the order is already complete.

## Steps to reproduce

1. Add at least one product to the cart and proceed through checkout to the payment step.
2. Select a valid payment method and fill in the required payment details.
3. Click the **Confirm** button.
4. Observe the success message and the button label.

## Actual behaviour (original)

- Clicking **Confirm** triggers payment validation against `/payment/check`.
- On success, a green alert is shown with the message **"Payment was successful"** (`data-test="payment-success-message"`).
- The button label remains **Confirm** (`pages.checkout.payment.confirm-btn`); it does not change to indicate a separate purchase/order step.
- The same button is used for the subsequent action that creates the invoice and shows the invoice number.
- Because payment validation and invoice creation were tied to the same control and label, customers could not tell from the UI that:
  - payment validation had already succeeded, and
  - a further click was required to complete the purchase and receive an invoice number.
- In some cases, the first click only completed payment validation; a second click on the still-labelled **Confirm** button was needed before the invoice confirmation appeared — without any visual change to the button text between those two steps.

## Expected behaviour

- After successful payment validation and the **"Payment was successful"** message, the button text should change from **Confirm** to **Buy now**.
- Only after clicking **Buy now** should the invoice be created and the invoice number be displayed in the order confirmation message.

## Impact

- Poor UX: the button label does not match the current step in the checkout flow.
- Risk of confusion: customers may believe the order is finished after seeing "Payment was successful", or may not understand that another action is required.
- Testing/acceptance ambiguity: automated and manual tests cannot reliably distinguish "confirm payment" from "complete purchase" using button text alone.

## Affected files

- `UI/src/app/checkout/payment/payment.component.html` — button label and click handler
- `UI/src/app/checkout/payment/payment.component.ts` — payment validation vs. invoice creation flow
- `UI/src/assets/i18n/*.json` — button label translations under `pages.checkout.payment`

## Out of scope

- Chat-widget checkout flow
- API or backend payment/invoice endpoints
- Changes outside `practice-software-testing/sprint5-holtesting/`
