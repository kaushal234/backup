Feature: Test Extranet user profiles

  Scenario: Request a single extranet user profiles without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_profiles/1"
    Then the response status code should be 401

  Scenario: Request all extranet users profiles is not an option
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_profiles"
    Then the response status code should be 403

  Scenario: Request a single extranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_profiles/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_profile/schemas/extranet_user_profile.json"

  Scenario: superuser users can't create an extranet user profile
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/extranet_user_profiles" with the body "tests/fixtures/json/sales/extranet_user_profile/dummies/post.json"
    Then the response status code should be 405

  Scenario: superuser users can't update an extranet user profile
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/extranet_user_profiles/5" with body:
    """
      {
        "department": "Indre et loire",
      }
    """
    Then the response status code should be 405

  Scenario: Extranet user can only see his profile
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_profiles/94"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/extranet_user_profile/schemas/extranet_user_profile.json"

  Scenario: Extranet user can only see his profile
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/extranet_user_profiles/15"
    Then the response status code should be 403