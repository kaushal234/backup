Feature: Test equipment hourmeter resets API

  Scenario: Request all equipment hourmeter resets without being authenticated
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets"
    Then the response status code should be 401

  Scenario: Request all equipment hourmeter resets
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_resets.json"
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_resets.json"

  Scenario: Request all equipment hourmeter resets should automatically filter on XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_resets.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Support\EquipmentHourmeterReset" is exposed on the API
    Then the filter "equipmentRecord" should be available and its type should be "string"
    And the filter "equipmentRecord.legacyId" should be available and its type should be "int"
    And the filter "equipmentRecord.serialNumber" should be available and its type should be "string"
    And the filter "createdBy" should be available and its type should be "string"

  Scenario: Request a single equipment hourmeter reset
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/equipment_hourmeter_resets/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_reset.json"

  Scenario: Create equipment hourmeter reset without perms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/post.json"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create equipment hourmeter reset with wrong dates should fail
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/post.json" and replace "hourmeterDate" by the date "-200 days"
    Then the response status code should be 422
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/post.json" and replace "hourmeterDate" by the date "+50 days"
    Then the response status code should be 422

  Scenario: Create equipment hourmeter reset
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/post.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_reset.json"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/1"
    And the JSON node "comment" should be equal to the string "A nice comment"
    And the JSON node "hourmeter" should be equal to the number 90
    # Users should not be not accessible
    And the JSON node "createdBy.@id" should be equal to the string "/sales/extranet_users/202"

  Scenario: Update equipment hourmeter reset without perms should fail
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_hourmeter_resets/4" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 403

  Scenario: Update equipment hourmeter reset without perms should fail
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_hourmeter_resets/4" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 403

  Scenario: Update equipment hourmeter reset
    Given I authenticate as the extranet user "user-reporter@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # We are updating the newly created
    When I send a "PUT" request to "/equipment_hourmeter_resets/4" with the body "tests/fixtures/json/equipment_hourmeter_reset/dummies/put.json" and replace "hourmeterDate" by the date "-5 days"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/equipment_hourmeter_reset/schemas/equipment_hourmeter_reset.json"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/3"
    And the JSON node "comment" should be equal to the string "Another nice comment"
    And the JSON node "hourmeter" should be equal to the number 84
    # Users should not be not accessible
    And the JSON node "createdBy.@id" should be equal to the string "/sales/extranet_users/202"

  Scenario: XU linked to an expired contract should not be allowed to create FUR on unit linked to this contract if not linked via CRT
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with body and replace "hourmeterDate" by the date "now":
    """
    {
      "equipmentRecord": "/equipment_records/14",
      "comment": "A nice comment",
      "hourmeter": 84,
      "hourmeterDate": "1984-01-07"
    }
    """
    Then the response status code should be 403

  Scenario: XU linked to an active contract as end user should be able to create FUR on unit linked to this contract
    Given I authenticate as the extranet user "contract-enduser@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with body and replace "hourmeterDate" by the date "now":
    """
    {
      "equipmentRecord": "/equipment_records/13",
      "comment": "A nice comment",
      "hourmeter": 8000,
      "hourmeterDate": "1984-01-07"
    }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/equipment_hourmeter_resets/5"

  Scenario: Update Unit Follow Up Report under contract when the user is linked as end user should be possible even if not the creator
    Given I authenticate as the extranet user "contract-enduser-extra@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
     # We are updating the newly created
    When I send a "PUT" request to "/equipment_hourmeter_resets/5" with body:
    """
    {
      "comment": "A nicer comment"
    }
    """
    Then the response status code should be 200

  Scenario: XU linked to an active contract as buyer should not be able to create FUR on unit linked to this contract
    Given I authenticate as the extranet user "contract-buyer@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/equipment_hourmeter_resets" with body and replace "hourmeterDate" by the date "now":
    """
    {
      "equipmentRecord": "/equipment_records/13",
      "comment": "A nice comment",
      "hourmeter": 8000,
      "hourmeterDate": "1984-01-07",
      "operationalStatus": "/unit_operational_statuses/MCF"
    }
    """
    Then the response status code should be 403

  Scenario: Update Unit Follow Up Report under contract when the user is linked as buyer should not be possible
    Given I authenticate as the extranet user "contract-buyer@extra.net"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/equipment_hourmeter_resets/5" with body:
     """
    {
      "comment": "A nicer comment"
    }
    """
    Then the response status code should be 403
