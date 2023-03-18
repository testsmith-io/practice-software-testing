# MISSION
Test the Contact form after implementation of the file upload

### Tester(s)
Rudolf Grötz, Matthias Zax

### Date
March 18th, 2023

### Timebox
50min

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
  - :heavy_check_mark: (OK) ACC1: I can attach a file via a file dialogue. 
    - Different browsers (file upload is browser special feature)
      - CHROME 99.12 / MAC OK
      - CHROME 99.11 / WIN OK
      - Safari 67.12 / MAC OK
      - EDGE 47.12 / WIN 11 OK
      
  - :heavy_check_mark: (OK) ACC2: Filename is not editable. 
    - Different browsers (file upload is browser special feature)
      - CHROME 99.12 / MAC OK
      - CHROME 99.11 / WIN OK
      - Safari 67.12 / MAC OK
      - EDGE 47.12 / WIN 11 OK
      
  - (NOK) ACC2: Only file type txt, pdf, jpg is allowed. 
    - valid: 
      - txt: OK
      - pdf: :bangbang: NOK - Error message: The file extension is incorrect, we only accept txt files.
      - jpg: :bangbang: NOK - Error message: The file extension is incorrect, we only accept txt files.
    - invalid: 
      - docx: :bangbang: NOK - Seems to be a general implementation error - therefore not checked
    
  - :heavy_check_mark: (OK) ACC3: Only valid file type can be selected via the file dialogue. 
    - try to select files
        CHROME / Safari / EDGE OK
    
  - (NOK) ACC4: File size must be > 0KB and <=500KB. 
    - valid: 
      - 1KB: OK - File will be attached and sent
      - 178KB: OK - File will be attached and sent
      - 500KB: OK - File will be attached and sent
      
    - invalid: 
      - 0KB: :bangbang: NOK - File will be sent
      - 501KB OK - Error message: File should be smaller than 500KB.
    
  - :heavy_check_mark: (OK) ACC5: If file is invalid then a error message must be displayed who describes the error.
    - Error message: The file extension is incorrect, we only accept txt files.
    - Error message: File should be smaller than 500KB.

#### Log
- see Test Ideas

#### Debriefing information
- Next test session should test the API, we assume that the integration between API and UI has errors

#### Findings/Defects
- see Test Ideas

#### Test Notes / Summary
- NA

#### Conclusions
- NA

#### Advice
- NA
