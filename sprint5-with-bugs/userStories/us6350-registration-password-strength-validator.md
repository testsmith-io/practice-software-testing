# User Story: Password Strength Validator on Registration


## User Story
As a new user regitring for an account,  
I want to receive real-time visual feedback about the validity and strength of my password while typing,  
so that I can create a secure password that fulfills all defined password requirements before completing the registration.


## Acceptance Criteria


### AC1 – Password input field is available
Given I am on the registration page  
Then a password input field is displayed.

---

### AC2 – Password requirements are visible
Given I am on the registration page  
Then a list of password requirements is displayed next to or below the password input field.

---

### AC3 – Defined password requirements
Given the password requirements are displayed  
Then they include the following rules:
- the password must be at least 8 characters long  
- the password must contain both uppercase and lowercase letters  
- the password must include at least one number  
- the password must include at least one special character  

---

### AC4 – Real-time validation feedback
Given I am entering a password  
When I type or modify characters in the password field  
Then the password requirements update immediately to reflect which rules are fulfilled or not fulfilled.

---

### AC5 – Minimum length validation
Given I am entering a password  
When the password contains fewer than the minimum required characters  
Then the minimum length requirement is shown as not fulfilled.

---

### AC6 – Character composition validation
Given I am entering a password  
When the password contains uppercase and lowercase letters, a number, and a special character  
Then the corresponding requirements are shown as fulfilled.

---

### AC7 – Password strength indicator is displayed
Given I am entering a password  
Then a password strength indicator is displayed below the password requirements.

---

### AC8 – Password strength levels
Given the password strength indicator is displayed  
Then it shows multiple strength levels, such as:
- Weak  
- Moderate  
- Strong  
- Very Strong  
- Excellent  

---

### AC9 – Dynamic strength update
Given I change the password input  
Then the password strength indicator updates dynamically according to the entered password.

---

### AC10 – Fully valid password feedback
Given all password requirements are fulfilled  
Then all requirements are indicated as fulfilled  
And the password strength indicator shows a strong or higher strength level.