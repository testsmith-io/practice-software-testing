# Sprint 5 (with bugs)

A variant of Sprint 5 with **90+ intentional bugs** embedded throughout the application. Designed for exploratory testing practice, bug-hunting exercises, and agile testing workshops.

## Purpose

- Practice exploratory testing techniques
- Learn to write effective bug reports
- Session-based testing exercises
- Agile testing workshop scenarios

## Differences from Sprint 5

- Contains 90+ intentional bugs across the UI, API, and accessibility
- Missing some Sprint 5 features (cart, social login, TOTP)
- Exposes a log endpoint for debugging

### Additional API Endpoint

| Method | Endpoint              | Description                 |
|--------|-----------------------|-----------------------------|
| GET    | `/logs/laravel.log`   | View application logs       |

## Hosted Version

| Component   | URL                                                                                     |
|-------------|-----------------------------------------------------------------------------------------|
| Application | [with-bugs.practicesoftwaretesting.com](https://with-bugs.practicesoftwaretesting.com)   |
| API         | [api-with-bugs.practicesoftwaretesting.com](https://api-with-bugs.practicesoftwaretesting.com) |
| Swagger     | [API Documentation](https://api-with-bugs.practicesoftwaretesting.com/api/documentation) |

## List of Known Bugs

### UI Bugs

| ID | Page | Title |
|----|------|-------|
| 1 | Cart | Plus / minus sign is not adding the amount |
| 2 | Category page | Sort works the other way around |
| 3 | Category page | Category "Chainsaw" leads to 404 |
| 4 | Category page | Some product names are aligned to the right |
| 5 | Category page | Title centered in Edge |
| 6 | Category page | Product names prevented from wrapping in Firefox |
| 7 | Checkout - cart | Delete button is disabled |
| 8 | Checkout - cart | Total price displays 0,00 |
| 9 | Checkout - billing address | Typo "Billing Address" |
| 10 | Checkout - billing address | Instead of postcode "Missing value" is displayed |
| 11 | Checkout - billing address | Submit / Next button has no text |
| 12 | Checkout - payment | Payment method dropdown shows "Error 304 - Missing Payment Gateway" |
| 13 | Invoice list | No pagination |
| 14 | Invoice page | Payment method shows "Method not found" |
| 15 | Invoice page | City is displayed in Country field and Country as City |
| 16 | Invoice page | Address fields display "undefined" |
| 17 | Login page | Email and password will not be validated |
| 18 | Login page | Incorrect Tab Order in Firefox |
| 19 | Login page | Input padding of email is not correct in Chrome |
| 20 | Login page | Button width is not correct in Firefox |
| 21 | Login page | User will be locked after 1 invalid attempt (should be 3) |
| 22 | Forgot password | Different font for the button in Edge and Chrome |
| 23 | Forgot password | Input padding of email is not correct in Chrome |
| 24 | Forgot password | Button width is not correct in Edge |
| 25 | Forgot password | Email syntax will not be checked |
| 26 | Home page | Some product images are not visible in Chrome |
| 27 | Home page | Price range selection doesn't work, max price is not sent to the server |
| 28 | Home page | Broken image instead of Toolshop logo |
| 29 | Home page | Home link in upper corner links to the contact page |
| 30 | Home page | Broken image instead of magnifying glass beside "Search" |
| 31 | Home page | Link text typo "Contakt" instead of "Contact" |
| 32 | Home page | "User Data not found" displayed instead of username |
| 33 | Home page | Typo "Sorth" instead of "Sort" |
| 34 | Home page | Typo "Serch" instead of "Search" on button |
| 35 | Home page | Product names prevented from wrapping in Firefox |
| 36 | Profile page | First Name displayed as Last Name and vice versa |
| 37 | Profile page | "City not found" displayed in city field |
| 38 | Profile page | Changing address fields leads to 404 or error |
| 39 | Profile page | Updated password is not hashed in DB, so it no longer works |
| 40 | Product detail | Plus / minus buttons do not change quantity |
| 41 | Product detail | "Add to favourites" displays error "Upsss... something wrong" |
| 42 | Product detail | Typo "Reltded products" instead of "Related products" |
| 43 | Product detail | "Add to cart" shows red error but items are still added |
| 44 | Product detail | Title aligned to the right in Firefox & Edge |
| 45 | Product detail | Buttons overlap in Chrome |
| 46 | Product detail | Badge text color is black |
| 47 | Product detail | Cannot add more than 10 pieces to cart |
| 48 | Rentals page | Bulldozer images don't work in Firefox and Edge |
| 49 | Rentals page | Title centered in Chrome |
| 50 | Registration page | Error reveals password hint for existing users |
| 51 | Registration page | Special characters in name cause "Invalid character" error |
| 52 | Registration page | State displays country and country displays state |
| 53 | Registration page | Incorrect Tab Order in Chrome |
| 54 | Registration page | Typo in Phone placeholder |
| 55 | Registration page | Different font for error messages in Edge and Chrome |
| 56 | Registration page | City label not aligned properly in Firefox |
| 57 | Registration page | Input padding of phone not correct in Firefox |
| 58 | Registration page | Button width not correct in Chrome |
| 59 | Registration page | Smaller font for some dropdown options |
| 60 | Contact page | Dropdowns display "Error 101" and "Error 202" |
| 61 | Contact page | Incorrect Tab Order in Firefox |
| 62 | Contact page | Typo in Message placeholder |
| 63 | Contact page | Different font for Attachment label in Edge and Chrome |
| 64 | Contact page | Email label not aligned properly in Firefox |
| 65 | Contact page | Input padding of firstname not correct in Firefox |
| 66 | Contact page | Button width not correct in Edge and Chrome |
| 67 | Contact page | Smaller font for some dropdown options |
| 68 | Contact page | PDF file upload not allowed |
| 69 | Contact page | JPG file upload not allowed |
| 70 | Contact page | File with 0KB can be uploaded |
| 84 | Emails | Logo used inconsistently in mails |

### API / Security Bugs

| ID | Area | Title | OWASP Category |
|----|------|-------|----------------|
| 71 | `users/{id}` | Can retrieve details from a different user by changing the ID | OWASP API1:2023 - Broken Object Level Authorization |
| 72 | Token / refresh token | Long-lived access token (260000 min / refresh 520000 min) | OWASP API2:2023 - Broken Authentication |
| 73 | `users` | Responses reveal `enabled` and `failed_login_attempts` to non-admins | OWASP API3:2023 - Broken Object Property Level Authorization |
| 74 | `brands` | Possible to delete a brand without admin token | OWASP API5:2023 - Broken Function Level Authorization |
| 75 | `products` | Responses reveal stock amount to non-admins | OWASP API3:2023 - Broken Object Property Level Authorization |
| 76 | `products` | Possible to delete a product without admin token | OWASP API5:2023 - Broken Function Level Authorization |
| 77 | `reports` | Too strict rate limiter returns 429 Too Many Requests | OWASP API4:2019 - Lack of Resources & Rate Limiting |
| 78 | `invoices` | All invoices returned regardless of token ownership | OWASP API5:2023 - Broken Function Level Authorization |
| 79 | `users/login` | SQL injection allows login as any user (append `' -- ` to email) | OWASP API8:2019 - Injection |
| 80 | `logs/laravel.log` | Application logs exposed through the web | OWASP API8:2023 - Security Misconfiguration |
| 81 | `invoices` | Can modify amount and price via POST | OWASP A04:2021 - Insecure Design |
| 82 | All IDs | IDs are incremental and guessable (vs ULIDs in non-bug version) | OWASP API1:2023 - Broken Object Level Authorization |
| 83 | Token roles | Wrong status code 401 instead of 403 for wrong role | OWASP API2:2023 - Broken Authentication |

### Accessibility Bugs

| ID | Area | Title |
|----|------|-------|
| 85 | Links | Contrast of links too low (4.26:1) |
| 86 | Checkout wizard | Contrast of checkout wizard labels too low (3.94:1) |
| 87 | Login form | No labels on form inputs |
| 88 | Checkout address form | No labels on form inputs |
| 89 | Checkout payment form | No labels on form inputs |
| 90 | Checkout login form | No labels on form inputs |
| 91 | Product detail | Quantity input has no label |
| 92 | Product detail | Product images have no alt text |
| 93 | Product overview | Search form has no labels |
| 94 | Product overview | No fieldset for filters |

### Discoverable Via Tour Types

| Tour | Description |
|------|-------------|
| Landmark Tour | Test key functional landmarks (buttons, inputs, counters) |
| Supporting Actor Tour | Test secondary UI elements (dropdowns, sort, filters) |
| SuperModel Tour | Visual/layout issues across browsers |
| FedEx Tour | Follow data end-to-end through the system |
| GarbageCollector Tour | Test all buttons and interactive elements |
| Intellectual Tour | Test with invalid, edge-case, or boundary data |
| BadBoy Tour | Security-focused testing (injection, authorization) |
| Links/Typos | Check all links and text for correctness |
| Accessibility | Test with screen readers, contrast checkers, keyboard nav |
