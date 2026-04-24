# US3203 - Allow Chinese character set in registration

## User Story:

As a shop admin,
I want to setup the UI with character set Big5-HKSCS
so that the UI also can be used by Chinese characters.

## Acceptance Criteria:

### UI Language
Given the web shop is displayed in the browser
When I select "Chinese" from the language dropdown list
Then the UI elements will be displayed in Chinese characters
and  the keyboard language switch to Chinese language
and  the interaction will be done in Chinese language.

### Location
Given the location of the user is within China
When  the homepage is loaded
Then  the used character set is Big5-HKSCS.

# Alternatives:

# Errors: