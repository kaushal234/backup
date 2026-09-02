@translator_document

Feature: Translator document

  Scenario: As a basic user, test that I'm allowed to go on translator page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/translator_document"
    Then the response status code should be 200

  @javascript
  Scenario: As a basic user, test that I'm allowed to see my documents
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/translator_document"
    Then I should be on "/translator_document"
    And I wait until I see "Documents"
    And I wait until I see "Upload Document"
    And I wait until I see "my_file_1.pdf"
    And I wait until I see "my_file_2.pdf"
    And I wait until I see "my_file_3.pdf"
    And I should not see "my_file_4.pdf"

