# ISSUE1245 - Checkout: payment succeeds but no order/invoice is created and cart is not cleared

## Description
After completing checkout and confirming payment, the UI shows "Payment was successful", but the order is never created, no invoice is generated, and items remain in the cart.

This makes checkout appear successful while the purchase is not actually completed.

## Environment
Sprint: sprint5-holtesting
Component: UI/src/app/checkout/payment/payment.component.ts
Affects: standard checkout flow (payment step → CONFIRM)

## Steps to reproduce
Log in (or continue as guest).
Add at least one product to the cart.
Go to checkout and complete cart, login/address steps.
On the payment step, select a payment method (e.g. Cash on delivery).
Click CONFIRM.

## Expected behavior
Payment validation succeeds.
An invoice/order is created via POST /invoices (or /invoices/guest for guest checkout).
The order confirmation screen is shown (paid = true, invoice number displayed).
The cart is emptied via cartService.emptyCart().
The order appears under account orders/invoices.

## Actual behavior
The inline success message "Payment was successful" is displayed.
No order confirmation screen appears.
No invoice/order is created.
Cart items remain unchanged.

## Root cause
In PaymentComponent.checkPayment(), payment validation is started asynchronously, but the method immediately returns of(this.state) before the HTTP request completes.

On the first CONFIRM click, this.state is still undefined, so finishFunction() receives a result that is not true and skips invoiceService.createInvoice(). The payment API call still completes afterward and sets paymentMessage, which is why the success message appears even though invoice creation never runs.

## Problematic flow:

User clicks CONFIRM
checkPayment() starts POST /payment/check
checkPayment() immediately emits undefined
createInvoice() is skipped
Payment API responds with "Payment was successful"
Success message is shown, but cart and orders are unchanged
The chat-widget checkout in the same project already handles this correctly by calling createInvoice() inside the payment validation callback.

## Proposed fix
Rewrite checkPayment() to return the validation Observable and emit true/false only af#ter the HTTP call completes:

Use paymentService.validate(...).pipe(map(...), catchError(...))
Remove the unused state caching field
Keep finishFunction() unchanged so invoice creation runs when validation emits true
File to change: sprint5-holtesting/UI/src/app/checkout/payment/payment.component.ts

## Acceptance criteria

 Clicking CONFIRM after successful payment creates an invoice/order

 Order confirmation screen is displayed with invoice number

 Cart is emptied after successful checkout

 Order is visible in account orders/invoices

 Failed payment validation still shows an error and does not create an invoice

## Notes
No backend/API change is required; invoice creation works when it is actually invoked.
Guest checkout and logged-in checkout should both be verified.