# Gift Card Validation (sprint 5)

At checkout, paying with a **gift card** requires a *gift card number* and a
*security (validation) code*. Previously these accepted any alphanumeric input
of any length, so random, empty, or unrealistically long values were happily
accepted and the order still reported **"Payment was successful"**.

Sprint 5 now validates both fields against an expected format **before the
payment is processed**, on the client *and* the server.

## Expected format

| Field                | Rule                                   | Example            |
|----------------------|----------------------------------------|--------------------|
| `gift_card_number`   | Exactly **16** alphanumeric characters | `1234567890123456` |
| `validation_code`    | Exactly **4** alphanumeric characters  | `1A2B`             |

Anything empty, non-alphanumeric, or of the wrong length (e.g. a 25-character
"number" or a one-character code) is rejected. The single source of truth for
these rules lives in `app/Payments/GiftCard.php`:

```php
GiftCard::NUMBER_REGEX; // /^[A-Za-z0-9]{16}$/
GiftCard::CODE_REGEX;   // /^[A-Za-z0-9]{4}$/
GiftCard::numberRules(); // ['required', 'string', 'regex:.../']
GiftCard::codeRules();
```

## Where it is enforced

Validation is applied in three layers so a bad gift card can never slip through:

1. **Checkout form (Angular)** — `payment.component.ts` sets the field
   validators (`pattern` + `required`) and the inputs are capped with
   `maxlength`. The user sees an inline, user-friendly message
   (`pages.checkout.payment.gift-card.number-error-format` /
   `…validation-code-error-format`) and the *Pay* button stays disabled while
   the input is invalid.

2. **Payment pre-check** — `POST /payment/check` re-validates the same format
   and returns **422** with validation errors instead of
   `"Payment was successful"` when the gift card is invalid.

3. **Order creation (authoritative)** — `POST /invoices`
   (`StoreInvoice::withValidator`) re-validates when
   `payment_method = gift-card`. This is the security boundary: if validation
   fails the request is rejected with **422** and **no invoice or payment row is
   created**, so the payment is never processed even if the pre-check is
   bypassed.

```
POST /invoices
{
  "payment_method": "gift-card",
  "payment_details": {
    "gift_card_number": "not-a-real-card",
    "validation_code": "1"
  },
  ...
}
→ 422 Unprocessable Entity
{ "payment_details.gift_card_number": ["..."],
  "payment_details.validation_code": ["..."] }
```

## Notes

- This is **format** validation, not balance/redemption. There is no gift card
  ledger in the demo app; the goal is to reject obviously invalid input, which
  is what the reported bug asked for.
- The `PaymentSeeder` generates demo gift cards in the same format
  (`Str::random(16)` / `Str::random(4)`).

## Tests

- `tests/Feature/PaymentTest.php` — valid format passes; an over-long number and
  a malformed code are each rejected with 422.
- `tests/Feature/InvoiceTest.php` — `it does not process an order paid with an
  invalid gift card` asserts the 422 **and** that no payment/invoice row is
  written.
