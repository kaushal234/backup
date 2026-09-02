Feature: Test Survey customer notifier

  Scenario: survey campaigns should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/campaigns"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/campaigns/1"
    Then the response status code should be 403

  Scenario: There should be only 3 existing campaigns
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/campaigns"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 3 element
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_campaigns/schemas/campaigns.json"

  Scenario: Add a new survey customer with all permissions should send an email
    Given I authenticate as the intranet user "user-gceo@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/surveys/models/1/publish" with the body "tests/fixtures/json/surveys/dummies/publish.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_campaigns/schemas/campaign_details.json"
    And the JSON node "description" should be equal to the string "This is a test campaign"
    And no email should have been sent asynchronously

  Scenario: There should be now 4 campaigns
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/campaigns"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 4 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_campaigns/schemas/campaigns.json"

  Scenario: Send a campaign
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PATCH" request to "/surveys/campaigns/3/send"
    Then the response status code should be 204
    And no email should have been sent asynchronously

  Scenario: I can view a single campaign
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/campaigns/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/survey_campaigns/schemas/campaign_details.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Survey\Campaign" is exposed on the API
    Then the filter "model" should be available and its type should be "string"

  Scenario: basic user can't edit a campaigns
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/campaigns/3" with body:
    """
    {
      "description": "I will Delaite you"
    }
    """
    Then the response status code should be 403

  Scenario: supersuer can edit a campaigns
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/surveys/campaigns/3" with body:
    """
    {
      "description": "I will Delaite you"
    }
    """
    Then the response status code should be 200

  Scenario: basic user can't delete a campaigns
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/campaigns/3"
    Then the response status code should be 403

  Scenario: superuser can delete campaigns
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/surveys/campaigns/3"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/surveys/published/3"
    Then the response status code should be 403





