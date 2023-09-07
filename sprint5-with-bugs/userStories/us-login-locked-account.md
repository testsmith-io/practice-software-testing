# US3370 - Login enhancement / Locked User
Background: User should be locked after 3 consecutive false attempts 

## User Story: Locked User 

As a shop admin,
I want that the user get locked after 3 consecutive false login attempts
to avoid a brute force attack.

## Acceptance Criteria:

### UC1: 3 consecutive failed login
GIVEN
    user did 3 consecutive invalid login attempts with the same email and wrong password
WHEN
    user try to login again with the same username and wrong password
THEN
    the message "User is locked - Login not possible!" will be displayed
AND
    the user stays on the login screen.

### UC2: 2 consecutive failed login
GIVEN
    user did 2 consecutive invalid login with the same username and wrong password
WHEN
    user try to login again with the same username and correct password
THEN
    the user is logged in.
