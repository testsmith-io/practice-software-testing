#
#
#
#

## create test idea for Customer Registration
- Already existing user
- Special characters (ÖÜÄöüä'```) in firstname / last name
- Wrong postcode for city
- Wrong state for country
- SQL-Attacks
- Password policy
- Valid E-Mail address
- Date of birth format
- 
## create test charter for Customer Registration
- Test idea: Special characters in first name last name
  - Explore 
    - Editing input fields in the registration UI
  - With 
    - Different character sets
  - To find
    - Localization issues
    - 
- Test idea: Password policy
  - Explore
      - Setting the password in UI (registration, profile, forgot password)
  - With
      - valid & invalid passwords with different character sets
  - To find
      - Localization issues within the password feature

## perform breaking news nightmare game
- Character set
  - Toolshop web-shop can handle orders with Cyrillic & Chinese characters. EU sues the host of the web-shop for distortion of competition for 2 million Euros.

## perform exploratory testing tour FedEx for User Profile
- Register a new customer and execute some CRUD actions and check if the data are handled correct (stored, deleted, updated)