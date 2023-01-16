# Title: Forgot Password
## User Story:

As a logged-out user,  
I want to be able to log in if I have forgotten my password  
so that I can access personal details.

## Acceptance Criteria:

### Scenario: User logs in using valid credentials
**Given** I'm a logged-out user and on the login page  
**When** I log in with valid credentials  
**Then** I am logged into my account

# Alternatives:
N/A

# Errors:

### Scenario: Email and password are mandatory
**Given** I'm a logged-out user and on the login page  
**When** I just hit the Login button  
**Then** the system notifies me that email and password are mandatory.

### Scenario: Email must be in correct format
**Given** I'm a logged-out user and on the login page  
**When** I fill in an email in the wrong format  
**Then** the system notifies me that the email-address is written in the wrong format.

### Scenario: Customer must exist in the database
**Given** I'm a logged-out user and on the login page  
**When** I try to login with an email-address in the correct format and some random password  
**Then** the system notifies me that there is something wrong with the given email-address or password.