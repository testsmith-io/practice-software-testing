# US6120 - Disable and Enable User Account
In a first step it is enough to have a user that is disabled
E-Mail: disabledUser@gmail.com
Password: 12345678910

## User Story
As an admin of ToolsShop, 
I want to have the ability to disable a user account and enable it 
so that I can manage user access effectively.

## Acceptance Criteria:

### Disabling a User:
When logged in as an admin, I can navigate to the user management section.
I can select a specific user from the list.
There should be an option to disable the user account.

After disabling the account, the user should immediately lose access to the system.
The disabled user should see an error message on login stating, "Account disabled."

### Enabling a Disabled User:
When logged in as an admin, I can navigate to the user management section.
I can select a specific user from the list, including those who are disabled.
There should be an option to enable the user account.

After enabling the account, the user should regain access to the system.
The re-enabled user should not see any error message on login.

### User Profile Visibility:
When a user account is disabled, the user should not be able to access their profile.
The admin, however, should still have access to the disabled user's profile for management purposes.
When a user account is enabled, the user should regain access to their profile.
