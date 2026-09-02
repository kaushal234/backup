Feature: Test changelog API

  Scenario: Request all change logs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/change_logs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/change_log/schemas/change_logs.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Module\ChangeLog" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Module\ChangeLog" is exposed on the API
    Then the filter "module" should be available and its type should be "string"
    And the filter "module.operationalOwner" should be available and its type should be "string"
    And the filter "author" should be available and its type should be "string"
    And the filter "type" should be available and its type should be "string"
    And the filter "ticket" should be available and its type should be "int"
    And the filter "message" should be available and its type should be "string"
    And the filter "order[date]" should be available and its type should be "string"
    And the filter "date[after]" should be available and its type should be "DateTimeInterface"
    And the filter "date[before]" should be available and its type should be "DateTimeInterface"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Download excel change log reports should be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/change_logs?columns=id,date,description,module.operationalOwner,author,ticket"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Date | Description | Moo | Author | Tts |

  Scenario: Edition is possible for allowed users on message, edition of a non-denormalized property is ignored
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/change_logs/1" with body:
    """
      {
        "type": "new_type",
        "message": "Un beau message"
      }
    """
    Then the response status code should be 200
    And the JSON node "message" should be equal to the string "Un beau message"
    And the JSON node "type" should not be equal to the string "new_type"
    And the JSON should be valid according to the schema "tests/fixtures/json/change_log/schemas/change_log.json"

  Scenario: Edition is not possible for not allowed users
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/change_logs/1" with body:
    """
    {
      "message": "Un beau message mais qui sera jamais enregistré"
    }
    """
    Then the response status code should be 403

