Feature: Test Question Survey Entity

  Scenario: Request all Questions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/question_survey_customer_service_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/question_survey_customer_service_records.json"

  Scenario: Request a single Question should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/question_survey_customer_service_records/1"
    Then the response status code should be 404