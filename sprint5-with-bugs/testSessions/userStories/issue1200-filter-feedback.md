# Improvement Issue – Testability & Automation

## Title

[Improvement]: Search product – Show number of results

## Context

- When searching for a product like "hammer", the UI displays product results, but there is no reliable and stable indicator for automation to validate the outcome like the exact number of results.

## Problem Statement

- No clear, machine-readable result
- Automated tests cannot reliably verify how many results were found and whether the correct results are displayed
- This leads to flaky tests and weak assertions

## Proposed Improvement

- Add a visible and stable element showing the number of results.

## Test Automation Value

- More reliable and stable automated tests
- Clear and verifiable expected results
- Improved maintainability of test automation

## Example Test Scenario

- Given 5 products with the name hammer in it exist in the system
- When user searches for "hammer"
- - Then message "5 products found for hammer" is shown

## Technical Considerations

- Existing search API could be used to retrieve the number of search results.
- Display a clear and visible result like "5 products found for hammer"
- Add stable selectors for search result count like data-testid="search-result-count"

## Risks

- Layout changes could impact element positions.
- API response (number of results) should be correctly shown in the frontend.

## Business Value

- Clear feedback for end users.
- Faster automation tests (no need for complex UI validations).
- Less flakier tests.
- Improved and reliable automation tests lead to faster feedback on defects and software quality.

## Acceptance Criteria

- Number of results are displayed in this format: "5 products found for 'hammer'".
- The displayed number of results matches the actual number of products returned by the API search.
- The message updates dynamically with every executed search on the page.
- The message has a stable selector for automation purposes.

