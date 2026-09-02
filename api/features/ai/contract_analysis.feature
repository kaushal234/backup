Feature: Test endpoints of AI analyze contract

  Scenario: Post a file to AI analyze should be possible for all users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/extract" with parameters:
    | key       | value        |
    | file  | @file.pdf   |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/contract/schemas/contract_analysis.json"

  Scenario: Post a wrong file to AI analyze should trhow validation error
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/extract" with parameters:
    | key       | value        |
    | file  | @file.doc   |
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string 'Unsupported file type. Allowed: "application/pdf"'

  Scenario: Post no file to AI analyze should trhow validation error
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/contracts/extract" with parameters:
    | key       | value        |
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string 'No file uploaded under "file" field'
