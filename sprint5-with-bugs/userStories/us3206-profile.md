# US3206 - Security enhancements in user profile
Background: Due to a wrong implemented function, currently the password is stored in plain text. 

## User Story: MD5 Hashed password 

As a shop admin,
I want that the password is stored hashed with MD5
to improve the security of the shop.

As a shop admin,
I want that the existing passwords are converted to hashed MD5
to improve the security of the shop.

## Acceptance Criteria:

### UC1: Set/Change via UI
#### registration: set new password
#### profile: change password 
#### login: change password
#### forgot password: 

### UC2: existing password
#### migration script for updating existing password

# Alternatives:

# Errors: