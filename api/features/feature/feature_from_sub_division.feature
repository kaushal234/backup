Feature: Test Features from divisions

  Scenario: Check grant with privilege gained from subdivision
    Given I authenticate as the intranet user "division-by-zero@tld-by-zero.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants?attributes=SUBDIVISION_FEATURE_DIVISION_BY_ZERO"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "grant": "GRANTED"
    }
    """

  Scenario: Check another subdivision's user is not granted this feature
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/grants?attributes=SUBDIVISION_FEATURE_DIVISION_BY_ZERO"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "grant": "DENIED"
    }
    """