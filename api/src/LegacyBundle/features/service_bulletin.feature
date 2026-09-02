Feature: Test Service Bulletin API

  Scenario: Request all Service Bulletins
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletins_requests.json"

  Scenario: Filters are declared on service bulletin
    Given the class "LegacyBundle\Entity\ServiceBulletin" is exposed on the API
    Then the filter "title" should be available and its type should be "string"
    Then the filter "description" should be available and its type should be "string"
    Then the filter "type" should be available and its type should be "string"
    Then the filter "lines.equipmentRecord.id" should be available and its type should be "int"
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[type]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"

  Scenario: As an extranet user I can get a Service Bulletin with its equipment lines
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/59"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletin_request.json"

  Scenario: As an extranet user I can get a Service Bulletin filtered by an accessible customer
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/59?customer=4074"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletin_request.json"

  Scenario: A user can use the simple search on quotes
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins?q=BRAKE"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletins_requests.json"

  Scenario: As an extranet user I can get the list of SB Files
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/service_bulletin_files"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletin_files.json"

  Scenario: As an extranet user I can get the list of SB Files filtered by SB
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/service_bulletin_files?parentId=58"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletin_files.json"

  Scenario: As an extranet user I have an error if a file not exist
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/legacy/service_bulletin_files/1"
    Then the response status code should be 404

  Scenario: As an extranet user I cannot see a Service Bulletin when no line has reached a customer-actionable status
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/101"
    Then the response status code should be 404

  Scenario: As an extranet user I can see a Service Bulletin once any of its lines reaches a customer-actionable status
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/102"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/LegacyBundle/fixtures/json/service_bulletin/schemas/service_bulletin_request.json"

  Scenario: As an extranet user I cannot see a confidential Service Bulletin even when a line is ready
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/103"
    Then the response status code should be 404

  Scenario: As an extranet user I cannot see a Service Bulletin with a non-visible status even when a line is ready
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/104"
    Then the response status code should be 404

  Scenario: As an extranet user I cannot see a Service Bulletin without an accessible equipment line even when another line is ready
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins/105"
    Then the response status code should be 404

  Scenario: As an extranet user the Service Bulletin list only surfaces bulletins that pass every visibility rule at once
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service_bulletins?q=KITREADYTEST"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to "1"
    And the JSON node "hydra:member[0].title" should be equal to "KITREADYTEST READY OTHER LINE"