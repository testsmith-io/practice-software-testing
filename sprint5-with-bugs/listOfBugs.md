
| ID  | Page           | Title                                                            | Description                                                                                                                                       | Expected Result                                                | Type          | Discoverable via | 
|-----|----------------|------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------|---------------|------------------|
|7 |checkout page - cart | Delete button is disabled | Delete button is disabled |NA |NA |GarbageCollector Tour (Button) |
|8 |checkout page - cart | Total price is not displayed | Total price value 00 is displayed|NA |NA |Landmark Tour |
|9 |checkout page - billing address | Billing Address typo | "Blliling Adress" displayed instead of "Billing Address"|NA |NA |Supporting Actor Tour |
|10 |checkout page - billing address | Postalcode "missing value"| Postalcode displays "missing value"|NA |NA |FedEx Tour |
|11 |checkout page - billing address | Button missing text | Button has no label |NA |NA |GarbageCollector Tour (Button)  |
|12 |checkout page - payment | Payment method "Error 304 - Missing Payment Gateway"|NA | NA|NA |Supporting Actor Tour |
| 28  | Profile Page | First Name / Last Name wrong displayed                           | First Name is displayed as Last Name and Last Name as First Name                    | NA                                                             | NA            | FedEx Tour       | 
| 29  | Profile Page | In City "City not found" is displayed                            | In City "City not found" is displayed                                               | NA                                                             | NA            | FedEx Tour       | 
| 30  | Profile Page | Change in profile raised 404                                     | Every change in profile of adress fields lead to a 404                              | NA                                                             | NA            | FedEx Tour       | 
| 31  | Profile Page | After update password login not possible anymore                 | Updated password is not hashed in the DB, so updated password doesn't work anymore  | NA                                                             | NA            | FedEx Tour       | 
| 43  | Registration Page | Wrong error message is displayed                                 | If the user is already registered the error message "User already registered - Your password hint is: Name of your cat!" is displayed             | Message "User already registered" is displayed                 | Error Message | tbd              |
| 44  | Registration Page | Special characters (like ÄÖÜ) are not allowed in first/last name | If firstname or lastname contains one of the characters "ÄäÜüÖö`'"&" then a error message "Invalid character" will be displayed and registration isnot be possible | Characters are allowed                                         | Type          | tbd              |
| 46  | Registration Pag | Country & State wrong content                                    | State displays the country and country displays the state                                                                                         | State isdisplayed in state and country is displayed in country | Data          | tbd              |
|     |                |                                                                  |                                                                                                                                                   |                                                                |               |                  | 
|     |                |                                                                  |                                                                                                                                                   |                                                                |               |                  | 
|     |                |                                                                  |                                                                                                                                                   |                                                                |               |                  | 
|     |                |                                                                  |                                                                                                                                                   |                                                                |               |                  | 







