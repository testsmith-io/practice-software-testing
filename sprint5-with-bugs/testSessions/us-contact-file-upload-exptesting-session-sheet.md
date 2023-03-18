# MISSION
Test the Contact form after implementation of the file upload

| Tester(s)                                | Date             | Timebox | Comment      |
|------------------------------------------|------------------|---------|--------------|
| Rudolf Grötz, Matthias Zax               | March 18th, 2023 | 50min   |              |

### Charter
#### UI
- Explore
    - file upload feature of the contact UI
- With
    - equivalence partitioning / boundary value analysis / decision table
- To discover
    - wrong implemented business rules (valid file types / size)

#### Test Ideas
- Acceptance Criteria from US:
  - :white_check_mark: (OK) ACC1: I can attach a file via a file dialogue. 
    - Different browsers (file upload is browser special feature)
      - :white_check_mark: CHROME 99.12 / MAC OK
      - :white_check_mark: CHROME 99.11 / WIN OK
      - :white_check_mark: Safari 67.12 / MAC OK
      - :white_check_mark: EDGE 47.12 / WIN 11 OK
      
  - :white_check_mark: (OK) ACC2: Filename is not editable. 
    - Different browsers (file upload is browser special feature)
      - :white_check_mark: CHROME 99.12 / MAC OK
      - :white_check_mark: CHROME 99.11 / WIN OK
      - :white_check_mark: Safari 67.12 / MAC OK
      - :white_check_mark: EDGE 47.12 / WIN 11 OK
      
  - :bangbang: (NOK) ACC2: Only file type txt, pdf, jpg is allowed. 
    - valid partition: 
      - txt: :white_check_mark: OK
      - pdf: :bangbang: NOK - Error message: The file extension is incorrect, we only accept txt files.
      - jpg: :bangbang: NOK - Error message: The file extension is incorrect, we only accept txt files.
    - invalid partition: 
      - docx: :bangbang: NOK - Seems to be a general implementation error - therefore not checked
    
  - :white_check_mark: (OK) ACC3: Only valid file type can be selected via the file dialogue. 
    - try to select files
      :white_check_mark: CHROME / Safari / EDGE OK
    
  - :bangbang: (NOK) ACC4: File size must be > 0KB and <=500KB. 
    - valid partition: 
      - :white_check_mark: 1KB: OK - File will be attached and sent
      - :white_check_mark: 178KB: OK - File will be attached and sent
      - :white_check_mark:500KB: OK - File will be attached and sent
      
    - invalid partition: 
      - :bangbang: 0KB: NOK - File will be sent
      - :white_check_mark: 501KB OK - Error message: File should be smaller than 500KB.
    
  - :white_check_mark: (OK) ACC5: If file is invalid then a error message must be displayed who describes the error.
    - :white_check_mark: Error message: The file extension is incorrect, we only accept txt files.
    - :white_check_mark: Error message: File should be smaller than 500KB.

#### Log
- see Test Ideas

#### Debriefing information
- Next test session should test the API, we assume that the integration between API and UI has errors

#### Findings/Defects
- :bangbang: BUG 47 - PDF file - Error message: The file extension is incorrect, we only accept txt files.
  - Exp Result: PDF can be uploaded
- :bangbang: BUG 48 - JPG file - Error message: The file extension is incorrect, we only accept txt files.
  - Exp Result: JPG can be uploaded
- :bangbang: BUG 49 - 0KB - File will be sent
  - Exp Result: Error Message "Filesize must be >0 " should be displayed

#### Test Notes / Summary
- NA

#### Conclusions
- NA

#### Advice
- NA
