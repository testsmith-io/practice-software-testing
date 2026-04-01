# GitHub Bug Report

## Title
- ID87: Login form has no labels

## Environment
- Local ToolShop `sprint5-with-bugs`
- URL: `http://localhost:4200/#/auth/login`
- OS: Linux
- Date: 2026-03-30

## Steps to Reproduce
1. Open `http://localhost:4200/#/auth/login`.
2. Go to the login form.
3. Inspect the form fields with browser developer tools or an accessibility tool.
4. Check whether the e-mail and password inputs have associated labels.

## Expected Result
- The login form should provide an associated label for each input field, either with a visible `<label>` element or an equivalent accessible name such as `aria-label` or `aria-labelledby`.
- Screen readers and other assistive technologies should be able to announce the purpose of each input clearly.

## Actual Result
- The login form uses placeholders (`Your E-mail *`, `Your password *`) but does not provide associated labels for the e-mail and password fields.
- This makes the form fail basic accessibility expectations and reduces usability for assistive technology users.

## Visual Proof (screenshots, videos, text)
- Exploratory session screenshot showed the login form with placeholders only.
- Template evidence from the local repository:
  - `sprint5-with-bugs/UI/src/app/auth/login/login.component.html`
  - The e-mail field is rendered as an `<input>` with a placeholder but no `<label>`.
  - The password field is rendered through `<app-password-input>` with a placeholder but no associated `<label>`.

## Notes
- This matches the known defect list entry: `Login form, no labels`.
- Suggested severity: Medium
- Category: Accessibility
