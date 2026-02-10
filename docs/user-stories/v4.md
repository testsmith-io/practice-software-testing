# User Stories – Sprint 4

Sprint 4 adds user authentication (login, register, forgot password), a protected customer account area (profile, favorites, invoices, messages), and sorting. All Sprint 3 features carry forward.

---

## Product Overview

### User Story
As a visitor,
I want to browse a paginated overview of all products with search, filtering, and sorting,
so that I can efficiently find products of interest.

### Acceptance Criteria

#### AC1 – Product grid is displayed
Given I navigate to the home page  
Then a grid of product cards is displayed  
And each card shows a product image, name, and price.  

---

#### AC2 – Navigating to product detail
Given the product overview is displayed  
When I click on a product card  
Then I am navigated to the product detail page.  

---

#### AC3 – Pagination
Given there are more products than fit on one page  
Then pagination controls are displayed below the product grid  
And clicking a page number updates the grid.  

---

#### AC4 – Search
Given I enter a valid search query (3–40 characters) and submit  
Then the product grid updates to show only matching products  
And all active filters are reset.  

---

#### AC5 – Category filter
Given I check one or more category checkboxes in the sidebar  
Then the product grid updates to show only products from those categories.  

---

#### AC6 – Brand filter
Given I check one or more brand checkboxes in the sidebar  
Then the product grid updates to show only products from those brands.  

---

#### AC7 – Sorting
Given I select a sort option (Name A-Z, Name Z-A, Price High-Low, Price Low-High)  
Then the product grid reloads with products ordered accordingly.  

---

## Product Detail

### User Story
As a visitor,
I want to view a product's details, add it to my cart, or save it to my favorites,
so that I can purchase it or come back to it later.

### Acceptance Criteria

#### AC1 – Product information shown
Given I am on the product detail page  
Then the product image, name, description, price, category badge, and brand badge are shown.  

---

#### AC2 – Quantity selector
Given the product is in stock  
Then a quantity input field is displayed with plus (+) and minus (-) buttons  
And the default quantity is 1.  

---

#### AC3 – Increase quantity
Given the quantity input is displayed  
When I click the plus button  
Then the quantity increases by 1.  

---

#### AC4 – Decrease quantity
Given the quantity is greater than 1  
When I click the minus button  
Then the quantity decreases by 1.  

---

#### AC5 – Minimum quantity
Given the quantity is 1  
When I click the minus button  
Then the quantity remains at 1.  

---

#### AC6 – Manual quantity entry
Given the quantity input is displayed  
When I type a number directly into the input field  
Then the quantity is updated to the entered value  
And the value is clamped between 1 and 999,999,999.  

---

#### AC7 – Add to cart
Given a valid quantity is selected  
When I click the "Add to Cart" button  
Then the product is added to the cart with the selected quantity  
And a success message "Product added to shopping cart." is displayed.  

---

#### AC8 – Out of stock
Given the product is not in stock and is not a rental item  
Then the "Add to Cart" button is disabled  
And "Out of stock" is shown in red.  

---

#### AC9 – Related products
Given the product detail page is displayed  
Then related products are shown below the main information.  

---

#### AC10 – Add to Favorites button
Given I am on the product detail page  
Then an "Add to Favorites" button is displayed.  

---

#### AC11 – Adding a favorite
Given I am logged in  
When I click "Add to Favorites"  
Then a success message "Product added to your favorites list." is displayed.  

---

#### AC12 – Duplicate favorite
Given the product is already in my favorites  
When I click "Add to Favorites"  
Then the message "Product already in your favorites list." is displayed.  

---

#### AC13 – Not logged in
Given I am not logged in  
When I click "Add to Favorites"  
Then the message "Unauthorized, can not add product to your favorite list." is displayed.  

---

## Rental Products

### User Story
As a visitor,
I want to browse products available for rent,
so that I can find tools I can rent by the hour.

### Acceptance Criteria

#### AC1 – Rentals page is accessible
Given I navigate to the rentals page  
Then a list of all rental products is displayed.  

---

#### AC2 – Rental product display
Given the rentals page is displayed  
Then each rental product shows a product image, name, and description.  

---

#### AC3 – Rental detail page
Given I click on a rental product  
Then the product detail page shows a duration slider (1–10 hours) instead of plus/minus buttons  
And the total price is calculated as the hourly rate multiplied by the selected duration.  

---

#### AC4 – Rental label in checkout
Given a rental item is in my cart  
Then the item is marked with "This is a rental item" in the checkout cart.  

---

## Checkout – Cart Review

### User Story
As a customer,
I want to review the items in my shopping cart,
so that I can verify my order is correct before proceeding.

### Acceptance Criteria

#### AC1 – Cart contents displayed
Given I have items in my cart  
When I navigate to the checkout page  
Then a table is displayed with columns: Item, Quantity, Price, Total, and Actions.  

---

#### AC2 – Update quantity
Given I change the quantity of a cart item  
Then the item total and cart total are recalculated  
And a confirmation message is displayed.  

---

#### AC3 – Delete item
Given I click the delete button on a cart item  
Then the item is removed from the cart  
And the cart total is recalculated.  

---

#### AC4 – Empty cart
Given I have no items in my cart  
Then the message "Your shopping cart is empty" is displayed.  

---

#### AC5 – Proceed
Given the cart contains at least one item  
When I click "Proceed"  
Then I advance to the next checkout step.  

---

## Checkout – Sign In

### User Story
As a user who is not logged in,
I want the possibility to log in during the checkout workflow,
so that I can complete my purchase without leaving the checkout.

### Acceptance Criteria

#### AC1 – Login step displayed for guests
Given I am not logged in  
And I am on the checkout page  
When I click "Proceed to Checkout" from the cart step  
Then a login form is displayed as the next step in the checkout wizard.  

---

#### AC2 – Login form fields
Given the checkout login step is displayed  
Then email and password fields are shown  
And a submit button is available.  

---

#### AC3 – Successful login during checkout
Given I enter valid credentials on the checkout login step  
When I submit the form  
Then I am authenticated  
And I can proceed to the billing address step.  

---

#### AC4 – Already logged in
Given I am already logged in  
When I reach the checkout login step  
Then a message "You are already signed in as [First Name] [Last Name]" is displayed  
And I can proceed directly to the billing address step.  

---

## Checkout – Billing Address

### User Story
As a customer,
I want to enter my billing address,
so that my invoice contains the correct address.

### Acceptance Criteria

#### AC1 – Address form fields
Given I am on the billing address step  
Then the following required fields are displayed:  
- Street (max 70 characters)
- City (max 40 characters)
- State (max 40 characters)
- Country (max 40 characters)
- Postal code (max 10 characters)

---

#### AC2 – Validation
Given I leave a required field empty  
Then the field is highlighted as invalid  
And the "Proceed" button is disabled.  

---

#### AC3 – Proceed to payment
Given all address fields are filled in  
When I click "Proceed"  
Then I advance to the payment step.  

---

## Checkout – Payment (Basic)

### User Story
As a customer,
I want to select a payment method and provide basic payment details,
so that I can complete my purchase.

### Acceptance Criteria

#### AC1 – Payment method selection
Given I am on the payment step  
Then a dropdown is displayed with options:  
- Bank Transfer
- Cash on Delivery
- Credit Card
- Buy Now Pay Later
- Gift Card

---

#### AC2 – Payment fields
Given I select a payment method  
Then account name and account number fields are displayed.  

---

#### AC3 – Successful order
Given I have selected a payment method and filled in the fields  
When I click the confirm button  
Then the order is placed  
And a confirmation message with the invoice number is displayed  
And the cart is cleared.  

---

## User Registration

### User Story
As a new visitor,
I want to create an account by providing my personal details,
so that I can log in and access features like checkout, favorites, and my account.

### Acceptance Criteria

#### AC1 – Registration page is accessible
Given I am not logged in  
When I navigate to the registration page  
Then a registration form is displayed.  

---

#### AC2 – Required fields
Given the registration form is displayed  
Then the following required fields are shown:  
- First name
- Last name
- Date of birth
- Address
- Postcode (exactly 5 digits)
- City
- State
- Country
- Phone
- Email
- Password

---

#### AC3 – Email validation
Given I enter an email address  
Then it must match a valid format (letters, numbers, dots, underscores, percent, plus, hyphen before @).  

---

#### AC4 – Password requirements
Given I enter a password  
Then it must be at least 6 characters long  
And no more than 40 characters.  

---

#### AC5 – Duplicate email
Given I submit the form with an email that is already registered  
Then the error message "Email is already in use." is displayed.  

---

#### AC6 – Successful registration
Given all fields are filled in with valid data  
When I submit the form  
Then the account is created  
And I am redirected to the login page.  

---

## User Login

### User Story
As a registered user,
I want to log in with my email and password,
so that I can access my account, favorites, invoices, and other authenticated features.

### Acceptance Criteria

#### AC1 – Login form
Given I navigate to the login page  
Then a form with email and password fields is displayed.  

---

#### AC2 – Email validation
Given I am on the login page  
Then the email field is required and must contain a valid email format.  

---

#### AC3 – Password validation
Given I am on the login page  
Then the password field is required.  

---

#### AC4 – Successful login
Given I enter valid credentials  
When I submit the login form  
Then I am authenticated and redirected to my account dashboard.  

---

#### AC5 – Invalid credentials
Given I enter an incorrect email or password  
When I submit the form  
Then the error message "Invalid email or password" is displayed.  

---

## Forgot Password

### User Story
As a registered user who has forgotten their password,
I want to request a password reset by providing my email,
so that I can regain access to my account.

### Acceptance Criteria

#### AC1 – Forgot password form
Given I click "Forgot password" on the login page  
Then a form with an email input is displayed.  

---

#### AC2 – Email validation
Given I enter an email address  
Then it must be in a valid format.  

---

#### AC3 – Successful reset
Given I enter a registered email  
When I submit the form  
Then a confirmation message is displayed and fades out after 3 seconds.  

---

#### AC4 – Non-existent email
Given I enter an unregistered email  
When I submit the form  
Then an error message is displayed.  

---

## Customer Profile

### User Story
As an authenticated user,
I want to view and update my personal information,
so that my account details are accurate.

### Acceptance Criteria

#### AC1 – Profile page is accessible
Given I am logged in  
When I navigate to my profile page  
Then my current profile information is displayed.  

---

#### AC2 – Editable fields
Given the profile page is displayed  
Then I can edit: first name, last name, phone, and address fields.  

---

#### AC3 – Read-only email
Given the profile page is displayed  
Then the email field is not editable.  

---

#### AC4 – Successful update
Given I modify profile fields with valid data  
When I save  
Then a success message is displayed.  

---

## Change Password

### User Story
As an authenticated user,
I want to change my password,
so that I can keep my account secure.

### Acceptance Criteria

#### AC1 – Change password form
Given I am on my profile page  
Then a change password section is displayed with:  
- Current password (required)
- New password (required)
- Confirm new password (required)

---

#### AC2 – Current password verification
Given I enter an incorrect current password  
When I submit the form  
Then the error "Your current password does not matches with the password." is displayed.  

---

#### AC3 – New password must differ
Given I enter a new password identical to the current one  
When I submit the form  
Then the error "New Password cannot be same as your current password." is displayed.  

---

#### AC4 – Passwords must match
Given the new password and confirmation do not match  
Then the error "Passwords do not match." is displayed.  

---

#### AC5 – Successful change
Given I provide a valid current password and a new password that matches the confirmation  
When I submit the form  
Then a success message is displayed  
And I am logged out after 5 seconds.  

---

## Favorites

### User Story
As an authenticated user,
I want to view and manage my list of favorite products,
so that I can quickly access products I'm interested in.

### Acceptance Criteria

#### AC1 – Favorites page
Given I am logged in  
When I navigate to my favorites page  
Then a list of my favorite products is displayed showing image, name, and description (truncated to 250 characters).  

---

#### AC2 – Empty favorites
Given I have no favorites  
Then a message indicating no favorites is displayed.  

---

#### AC3 – Remove a favorite
Given the favorites list is displayed  
When I click the delete button on a favorite  
Then the product is removed and the list refreshes.  

---

## Invoices

### User Story
As an authenticated user,
I want to view my past orders,
so that I can track my purchases.

### Acceptance Criteria

#### AC1 – Invoice list
Given I am logged in  
When I navigate to my invoices page  
Then a paginated table is displayed with columns: invoice number, billing street, invoice date, total, and a details link.  

---

#### AC2 – Invoice detail
Given I click on an invoice  
Then the detail page shows:  
- invoice number, date, and total
- billing address (street, postal code, city, state, country)
- payment method and details
- product line items (quantity, name, price, line total)

---

#### AC3 – Non-existent invoice
Given the invoice does not exist or does not belong to me  
Then a "not found" message is displayed.  

---

## Messages

### User Story
As an authenticated user,
I want to view my contact messages and reply to them,
so that I can follow up on my inquiries.

### Acceptance Criteria

#### AC1 – Messages list
Given I am logged in  
When I navigate to my messages page  
Then a paginated table is displayed with columns: subject, message (truncated to 50 chars), status badge (NEW / IN_PROGRESS / RESOLVED), date, and a details link.  

---

#### AC2 – Message detail
Given I click on a message  
Then the original message is displayed (sender, subject, status, full text, timestamp)  
And any replies are listed chronologically below.  

---

#### AC3 – Reply to message
Given I am on the message detail page  
When I enter a reply and submit  
Then the reply is added and the replies list is updated.  

---

## Browse Products by Category

### User Story
As a visitor,
I want to browse products within a specific category with filtering, sorting, and pagination,
so that I can find specific products within a category.

### Acceptance Criteria

#### AC1 – Category page is displayed
Given I click on a category name  
Then a page with products belonging to that category is displayed  
And the category name is shown as the page title.  

---

#### AC2 – Filters available
Given the category page is displayed  
Then the same filters as the product overview are available:  
- category checkboxes (subcategory tree)
- brand checkboxes
- sorting dropdown
- pagination controls

---

## Contact Form

### User Story
As a visitor,
I want to send a message through a contact form,
so that I can reach out for support or other inquiries.

### Acceptance Criteria

#### AC1 – Contact form is accessible
Given I navigate to the contact page  
Then a contact form is displayed.  

---

#### AC2 – Required fields
Given the contact form is displayed  
Then the following fields are shown:  
- First name (required)
- Last name (required)
- Email (required, must be valid format)
- Subject (required, dropdown)
- Message (required, minimum 50 characters)

---

#### AC3 – Subject options
Given the subject dropdown is displayed  
Then it includes the following options:  
- Customer service
- Webmaster
- Return
- Payments
- Warranty
- Status of order

---

#### AC4 – Message minimum length
Given I enter a message with fewer than 50 characters  
Then a validation error is shown indicating the message must be at least 50 characters.  

---

#### AC5 – Successful submission
Given all required fields are filled in with valid data  
When I submit the contact form  
Then a confirmation message is displayed  
And the form is hidden.  
