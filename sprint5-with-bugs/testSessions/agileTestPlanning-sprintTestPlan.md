# Sprint Test Plan
## Introduction
- This document gives an overview about the test approach in sprint 05 for the Test Smith app. 
- The tests will be executed in form of scripted tests and exploratory tests.
- Regression tests will be done with automated tests.
- This test cycle is the final User Acceptance Test and will be the input for the Go-Live decision.
## Risks
- The new payment gateway can only be tested on the production system. If the test fail, we must execute a redeployment without the new gateway. Since there is the possibility to have a downtime, the deployment to production must be planned and executed between 9am-11am CEST.
## In Scope
- 3203: Allow Chines character et in registration
- 3206: Security Enhancements in user profile
- 3207: Implement new payment methods China Pay
- 3411: Implement additional step (login) in checkout flow
- 3424: Implement new feature “Contact” added
- 3490: Open-source library auth-X32 must be replaced by auth-XX64 due to critical vulnerabilities findings
- 5003: OR-Mapper replacement version 1.2 with 2.0 (major release)
## Out of Scope
- 4555: Typos in imprint (will not be tested due to low risk)
- 3489: New feature “Contact” (only the UI for UX tests, will not be enabled – UAT in next sprint when the APIs are available)
## Test Objects
- Web-UI
- Use Cases
- Payment Gateway
- Data tables in DB
## Test Objectives
- Quality Attributes ISO-9126
- Functionality
- Usability
- Performance
- Quality Attributes ISO-25001 (Data)
- Data Integrity
- Data Consistency
## Test Stages
- Component Test (not in scope)
- System Test (not in scope)
- System Integration Test (Payment Gateway is mocked)
- User Acceptance Test
The finale validation is only possible after deployment to production since we do not have a test payment gateway.
## Test Documentation
- Test Plans
  - Product Test Plan <Link to Product Test Plan>.
  - Sprint Test Plan <this document>.
- Test Specifications are available in Jira <Link to Test Set>.
- Test Summary Report are available in Confluence <Link to Test Summary Report>
## Test Environments
- SYS01-Test (System Test & System Integration Test)
  - Test Data Set USER-3245-SYS01 must be installed.
- SYS02-PreProd (User Acceptance Tests)
  - Test Data Set USER-3245-SYS02 must be installed.
- PROD12 (User Acceptance Test for payment gateway)
  - RBI-UAT user must be activated for test. After the test the RBI-UAT user must be deactivated.
## Responsibilities
- The new data model must be tested by the Data Engineers.
- Service Desk must communicate and control the downtime for the tests of the payment gateway.

