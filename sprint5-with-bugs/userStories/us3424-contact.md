# US3424 - Contact Form
Background: Until now, the user can only communicate with the shop owner via e-mail.
The user should also have the possibility to contact the shop owner via a contact form.

## User Story: Contact with the shop owner
As a user who 
I want the possibility to send a message to the shop owner via an UI-form
In order not to have to switch to the e-mail program to write an e-mail.

## Acceptance Criteria:

### UC1: User send message
Given the user is on any UI of web shop
When  the user clicks "Contact"
Then  a contact form will be displayed. 

Given the user is on the contact UI
When  the user filled out at least all mandatory fields
And   clicks "Send" an e-mail will sent to the show owner.

ACC1: Contact link is displayed in the main menu
ACC2: Fields are displayed: First Name / Last Name, EMail address, Subject / Message
ACC3: If a mandatory field (marked with *) has no value then a error message must be dispalyed
ACC4: Only valid E-Mail is accepted
ACC5: Subject only allows values select from ListOfValue (Customer Service, Webmaster, General, Info)



# Alternatives:

# Errors: