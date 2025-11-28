# CTF Flags Summary - OWASP API Top 10 Security Vulnerabilities

This document lists all CTF flags embedded in the sprint5-with-bugs API for the OWASP API Top 10 workshop.

**Note**: All flags now use a consistent structure:
```json
{
  "ctf": {
    "flag": "FLAG{...}",
    "vulnerability_description": "...",
    "sequence": 1,
    "binary_code": "01110011"
  }
}
```

## 🧩 Binary Puzzle - Hidden Message

Each flag contains a `sequence` number (1-11) and a `binary_code` (8-bit binary).

**Challenge**: Collect all 11 flags, sort them by sequence number, and convert the binary codes from binary to ASCII text to reveal a secret message!

**Hint**: When you decode all binary codes in order, you'll get an important security reminder...

**Solution**:
1. Sequence 1: `01110011` → s
2. Sequence 2: `01100101` → e
3. Sequence 3: `01100011` → c
4. Sequence 4: `01110101` → u
5. Sequence 5: `01110010` → r
6. Sequence 6: `01100101` → e
7. Sequence 7: `00100000` → (space)
8. Sequence 8: `01100001` → a
9. Sequence 9: `01110000` → p
10. Sequence 10: `01101001` → i
11. Sequence 11: `00100001` → !

**Message**: **secureyourapi!**

## Implemented Flags

### Bug 71: OWASP API1:2023 - Broken Object Level Authorization (BOLA)
- **Endpoint**: `GET /api/users/{id}`
- **Flag**: `FLAG{API1_2023_BROKEN_OBJECT_LEVEL_AUTHORIZATION}`
- **Sequence**: 1 | **Binary**: `01110011` (s)
- **How to capture**: Access another user's data by changing the user ID while authenticated
- **Description**: "You can retrieve another user's details by changing the user ID. The API does not verify that the authenticated user has permission to access this specific user's data."
- **File**: `app/Http/Controllers/UserController.php:570`

### Bug 72: OWASP API2:2023 - Broken Authentication (Long-lived tokens)
- **Endpoint**: `POST /api/users/login`
- **Flag**: `FLAG{API2_2023_BROKEN_AUTHENTICATION_LONG_LIVED_TOKEN}`
- **Sequence**: 2 | **Binary**: `01100101` (e)
- **How to capture**: Login successfully and inspect the token response (token=260000 mins, refresh=520000 mins)
- **Description**: "Access token TTL is set to 260000 minutes (~180 days) and refresh token to 520000 minutes (~361 days). Tokens should expire much sooner for security."
- **Response property**: `ctf` (always present on successful login)
- **File**: `app/Http/Controllers/UserController.php:305`

### Bug 73: OWASP API3:2023 - Broken Object Property Level Authorization
- **Endpoint**: `GET /api/users`
- **Flag**: `FLAG{API3_2023_BROKEN_OBJECT_PROPERTY_LEVEL_AUTHORIZATION_USERS}`
- **Sequence**: 3 | **Binary**: `01100011` (c)
- **How to capture**: Access the users endpoint and observe exposed fields like `enabled` and `failed_login_attempts`
- **Description**: "The enabled and failed_login_attempts fields are exposed in the response. These should only be visible to admins."
- **File**: `app/Http/Controllers/UserController.php:78`

### Bug 74: OWASP API5:2023 - Broken Function Level Authorization (Brand)
- **Endpoint**: `DELETE /api/brands/{id}`
- **Flag**: `FLAG{API5_2023_BROKEN_FUNCTION_LEVEL_AUTHORIZATION_BRAND}`
- **Sequence**: 4 | **Binary**: `01110101` (u)
- **How to capture**: Delete a brand using a non-admin token
- **Description**: "Non-admin users can delete brands. This endpoint should require admin role but does not properly enforce it."
- **File**: `app/Http/Controllers/BrandController.php:222`

### Bug 75: OWASP API3:2023 - Broken Object Property Level Authorization (Stock)
- **Endpoint**: `GET /api/products/{id}`
- **Flag**: `FLAG{API3_2023_BROKEN_OBJECT_PROPERTY_LEVEL_AUTHORIZATION_STOCK}`
- **Sequence**: 5 | **Binary**: `01110010` (r)
- **How to capture**: Access product details and observe stock information exposed to non-admin users
- **Description**: "The product response exposes the stock quantity field. This sensitive inventory information should only be visible to admin users."
- **File**: `app/Http/Controllers/ProductController.php:218`

### Bug 76: OWASP API5:2023 - Broken Function Level Authorization (Product)
- **Endpoint**: `DELETE /api/products/{id}`
- **Flag**: `FLAG{API5_2023_BROKEN_FUNCTION_LEVEL_AUTHORIZATION_PRODUCT}`
- **Sequence**: 6 | **Binary**: `01100101` (e)
- **How to capture**: Delete a product using a non-admin token
- **Description**: "Non-admin users can delete products. This endpoint should require admin role but does not properly enforce it."
- **File**: `app/Http/Controllers/ProductController.php:394`

### Bug 77: OWASP API4:2019 - Lack of Resources & Rate Limiting
- **Endpoint**: `GET /api/reports/total-sales-per-country`
- **Flag**: `FLAG{API4_2019_LACK_OF_RESOURCES_RATE_LIMITING}`
- **Sequence**: 7 | **Binary**: `00100000` (space)
- **How to capture**: Access any report endpoint (rate limiting is too strict, returns 429)
- **Description**: "The reports endpoint has overly restrictive rate limiting that returns 429 Too Many Requests too frequently. This impacts legitimate users trying to access reports."
- **File**: `app/Http/Controllers/ReportController.php:64`

### Bug 78: OWASP API5:2023 - Broken Function Level Authorization (Invoice)
- **Endpoint**: `GET /api/invoices`
- **Flag**: `FLAG{API5_2023_BROKEN_FUNCTION_LEVEL_AUTHORIZATION_INVOICE}`
- **Sequence**: 8 | **Binary**: `01100001` (a)
- **How to capture**: Access the invoices endpoint and observe all invoices being returned (not filtered by user_id)
- **Description**: "All invoices are returned regardless of the authenticated user. The endpoint should only return invoices belonging to the current user."
- **File**: `app/Http/Controllers/InvoiceController.php:72`

### Bug 79: OWASP API8:2019 - Injection (SQL Injection)
- **Endpoint**: `POST /api/users/login`
- **Flag**: `FLAG{API8_2019_INJECTION_SQL}`
- **Sequence**: 9 | **Binary**: `01110000` (p)
- **How to capture**: Login using SQL injection (e.g., `admin@example.com' -- ` as email)
- **Description**: "The login endpoint is vulnerable to SQL injection. Using ' -- in the email bypasses password verification."
- **Response property**: `ctf_sql_injection` (only present when SQL injection is detected)
- **File**: `app/Http/Controllers/UserController.php:296`

### Bug 80: OWASP API8:2023 - Security Misconfiguration (Log Exposure)
- **Endpoint**: `GET /api/logs/laravel.log`
- **Flag**: `FLAG{API8_2023_SECURITY_MISCONFIGURATION_LOG_EXPOSURE}`
- **Sequence**: 10 | **Binary**: `01101001` (i)
- **How to capture**: Access the exposed log file endpoint
- **Description**: "Application logs are publicly accessible via the web. Logs may contain sensitive information like API keys, user data, and internal system details. This endpoint should be disabled or properly secured."
- **File**: `routes/api.php:46`

### Bug 81: OWASP A04:2021 – Insecure Design (Price Manipulation)
- **Endpoint**: `POST /api/invoices`
- **Flag**: `FLAG{A04_2021_INSECURE_DESIGN_PRICE_MANIPULATION}`
- **Sequence**: 11 | **Binary**: `00100001` (!)
- **How to capture**: Create an invoice with manipulated `unit_price` values in invoice_items
- **Description**: "The API accepts client-provided unit_price values in invoice_items. Prices should be validated server-side against actual product prices to prevent manipulation."
- **File**: `app/Http/Controllers/InvoiceController.php:150`

## Pending Flags (Not Yet Implemented)

### Bug 82: OWASP API1:2023 - Broken Object Level Authorization (Predictable IDs)
- **Description**: IDs are incremental and easy to guess (should be ULIDs)
- **Status**: General vulnerability across all endpoints

### Bug 83: OWASP API2:2023 - Broken Authentication (Wrong Status Code)
- **Description**: Returns 401 instead of 403 for authorization failures
- **Status**: General vulnerability across all endpoints

## Special Cases

### Login Endpoint - Multiple Flags
The `POST /api/users/login` endpoint can return multiple flags:
- `ctf`: Always present on successful login (long-lived token vulnerability)
- `ctf_sql_injection`: Only present when SQL injection is detected in the email field

Example response with SQL injection:
```json
{
  "access_token": "...",
  "token_type": "bearer",
  "expires_in": 15600000,
  "ctf_sql_injection": {
    "flag": "FLAG{API8_2019_INJECTION_SQL}",
    "vulnerability_description": "..."
  },
  "ctf": {
    "flag": "FLAG{API2_2023_BROKEN_AUTHENTICATION_LONG_LIVED_TOKEN}",
    "vulnerability_description": "..."
  }
}
```

## Usage Instructions

1. **For Workshop Participants**: Use the bug-hunting-guide.html to explore vulnerabilities and discover flags
2. **For CTF Mode**: Collect all flags by exploiting the documented vulnerabilities
3. **Flag Format**: All flags follow the pattern `FLAG{OWASP_CATEGORY_DESCRIPTION}`

## Flag Validation

Total flags available: **11 flags**

Participants should be able to collect all flags by following the OWASP API Top 10 security testing methodology.

## Binary Puzzle Walkthrough

Once you've collected all 11 flags, follow these steps to decode the hidden message:

1. **Extract the data**: For each flag, note the `sequence` number and `binary_code`
2. **Sort by sequence**: Arrange the flags in order from sequence 1 to 11
3. **Decode binary to ASCII**: Convert each 8-bit binary code to its ASCII character
4. **Reveal the message**: Combine all characters to read "secureyourapi!"

**Quick binary to ASCII conversion:**
- Online tool: Use any "binary to text" converter
- Python: `chr(int('01110011', 2))` → 's'
- Manual: Each 8-bit binary represents one ASCII character

This puzzle reinforces an important principle: **Always secure your API!**
