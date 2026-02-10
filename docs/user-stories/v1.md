# User Stories – Sprint 1

Sprint 1 delivers the foundation: a basic product catalog with browsing, category pages, and a simple contact form.

---

## Product Overview

### User Story
As a visitor,
I want to see an overview of all available products,
so that I can browse the catalog and find items of interest.

### Acceptance Criteria

#### AC1 – Product overview is displayed
Given I navigate to the home page  
Then a grid of product cards is displayed showing all products.  

---

#### AC2 – Product card information
Given the product overview is displayed  
Then each product card shows:  
- a product image
- the product name
- the product price

---

#### AC3 – Navigating to product detail
Given the product overview is displayed  
When I click on a product card  
Then I am navigated to the product detail page for that product.  

---

## Product Detail

### User Story
As a visitor,
I want to view the full details of a product,
so that I can learn more about it before deciding to purchase.

### Acceptance Criteria

#### AC1 – Product detail page is displayed
Given I click on a product from the overview or category page  
Then the product detail page is displayed.  

---

#### AC2 – Product information shown
Given the product detail page is displayed  
Then the following information is shown:  
- product image
- product name
- product description
- product price
- category badge
- brand badge

---

#### AC3 – Related products
Given the product detail page is displayed  
Then a section with related products is shown below the main product information  
And each related product is clickable and navigates to its detail page.  

---

## Browse Products by Category

### User Story
As a visitor,
I want to browse products within a specific category,
so that I can find products of a particular type.

### Acceptance Criteria

#### AC1 – Category page is displayed
Given I click on a category name  
Then a page with products belonging to that category is displayed.  

---

#### AC2 – Category title
Given the category page is displayed  
Then the category name is shown as the page title.  

---

#### AC3 – Products from selected category
Given the category page is displayed  
Then only products belonging to the selected category are shown.  

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
