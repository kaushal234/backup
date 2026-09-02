Feature: Test User Setting entity

  Scenario: Filters are declared on resource
    Given the class "App\Entity\UserSetting" is exposed on the API
    And the filter "user" should be available and its type should be "string"
    And the filter "name" should be available and its type should be "string"

  Scenario: Request all user settings be possible but it should be filtered
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/user_settings"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 0 element
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/user_settings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_setting/schemas/user_settings.json"
    And the JSON node "hydra:member" should have 1 element

  Scenario: Request a single user setting should only be possible for mine
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/user_settings/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/user_settings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/user_setting/schemas/user_setting.json"

  Scenario: Create a single user setting should only be possible for mine
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user_settings" with body:
    """
    {
      "name": "whse.settings",
      "user": "/people/99",
      "settings": { "sso": "/locations/28" }
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/user_settings" with body:
    """
    {
      "name": "whse.settings",
      "user": "/people/99",
      "settings": { "sso": "/locations/28" }
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/user_setting/schemas/user_setting.json"

  Scenario: Edit should not be possible to not allow manipulation of json
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/user_settings/1" with body:
    """
    {
      "name": "whse.settings",
      "user": "/people/99",
      "settings": { "sso": "/locations/28" }
    }
    """
    Then the response status code should be 405
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/user_settings/1" with body:
    """
    {
      "name": "tts.settings",
      "user": "/people/102",
      "settings": { "type": "/mis/types/7" }
    }
    """
    Then the response status code should be 405

  Scenario: Delete a single user setting should only be possible for mine
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/user_settings/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/user_settings/2"
    Then the response status code should be 204
