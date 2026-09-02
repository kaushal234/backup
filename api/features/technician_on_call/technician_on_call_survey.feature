Feature: Test Technician On Call

  Scenario: As basic user, I should be able to show a survey
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/technician_on_call_surveys/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/technician_on_calls/schemas/technician_on_call_survey.json"

  Scenario: As basic user, I should not be able to create survey on open TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/1"
    }
    """
    Then the response status code should be 403

  Scenario: As csm user, I should not be able to create a TOC survey with wrong data
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to the string "ConstraintViolation"

  Scenario: As csm user, I should not be able to create survey on open TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to the string "ConstraintViolation"
    And the JSON node "detail" should be equal to the string "TOC must be SOLVED or CLOSED to create a Survey."

  Scenario: As csm user, I should be able to create survey on close TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 5,
        "responsiveness": 5,
        "communication": 5,
        "attitude": 5,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/10"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/TOC#.* - Survey results from extranet/"

  Scenario: As csm user, when I create a survey with bad result, an email should be sent
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/19"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/Task#.* opened following TOC Survey Below 5\/5\/5\/5/"
    Given I authenticate as the intranet user "user-csm@tld.fr"
    When I send a "GET" request to "/tasks?module.name=TOC&referenceId=19"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].shortDescription" should be equal to the string "TOC Survey Below 5/5/5/5"

  Scenario: As csm user, I should not be able to create survey when TOC already have one
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/10"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to the string "ConstraintViolation"
    And the JSON node "detail" should be equal to the string "technicianOnCall: This value is already used."

  Scenario: As basic user, I should not be able to edit survey on open TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_call_surveys/1" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK"
    }
    """
    Then the response status code should be 403

  Scenario: As csm user, I should be able to edit survey on close TOC
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/technician_on_call_surveys/1" with body:
    """
    {
        "execution": 5,
        "responsiveness": 5,
        "communication": 5,
        "attitude": 5,
        "comment": "OK"
    }
    """
    Then the response status code should be 200

  Scenario: As csm user, when I create a bad survey and some error occurred during the task creation, an email should be sent
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/43"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/.* Bad Survey error occurred during task creation/"

  Scenario: As an anonymous user, I cannot create a survey without the correct token
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/42",
        "token": ""
    }
    """
    Then the response status code should be 401

  Scenario: As an anonymous user, I can create a survey with the correct token
    Given I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/42",
        "token": "token"
    }
    """
    Then the response status code should be 201

  Scenario: As an extranet user, I can create a survey with the correct token
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 2,
        "responsiveness": 1,
        "communication": 2,
        "attitude": 3,
        "comment": "OKKKKK",
        "technicianOnCall": "/service/technician_on_calls/45",
        "token": "token"
    }
    """
    Then the response status code should be 201

  Scenario: As an extranet user, I can create a survey on a TOC created by an extranet user and the email is sent correctly
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/technician_on_call_surveys" with body:
    """
    {
        "execution": 4,
        "responsiveness": 5,
        "communication": 4,
        "attitude": 5,
        "comment": "Good intervention",
        "technicianOnCall": "/service/technician_on_calls/47",
        "token": "fixtureTokenForSurveyPublicLink"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/TOC#47.* - Survey results from extranet/"