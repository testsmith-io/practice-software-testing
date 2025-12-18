# User Story: Password Strength Validator on Registration

## Story
As a visitor registering for a new account,  
I want to receive real-time feedback about the strength and validity of my password,  
so that I can create a secure password that meets all requirements and successfully complete the registration.

## Acceptance Criteria

### AC1 - Minimum password length
Given I am on the registration page,  
When I enter a password with fewer than 8 characters,  
Then the password strength validator indicates that the minimum length requirement is not met,  
And the password is considered invalid.

### AC2 - Uppercase and lowercase letters
Given I am on the registration page,
When the password does not contain both uppercase and lowercase letters,
Then the password is marked as invalid.


### AC3 - Numeric character required
Given I am on the registration page,
When the password does not contain at least one number,
Then the password is marked as invalid.

### AC4 - Special symbol required
Given I am on the registration page,
When the password does not contain at least one special symbol,
Then the password is marked as invalid.

### AC5- Valid password
Given I am on the registration page,
When the password meets all password requirements,
Then the password is marked as valid
And the Register button is enabled.
