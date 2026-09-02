Feature: Test DMS API

  Scenario: As basic user I should be able to see non confidential DMS
    Given I authenticate as the intranet user "user-service@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/10"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms.json"

  Scenario: Request a single DMS should be available for evendors user when DMS is not confidential and portal is EVENDORS
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/1"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/4"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms.json"

  Scenario: Request a single DMS should be available for extranet user when DMS is not confidential and portal is EXTRANET
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/1"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms.json"

  Scenario: Request all DMS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms_collection.json"

  Scenario: Request a single DMS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms.json"

  Scenario: Request a single DMS - content negotiation
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.alvest.dms"
    When I send a "GET" request to "/dms/1"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/vnd.ms-excel"
    Then the header "Content-Disposition" should be equal to "inline; filename=DMS_1234.xls"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.alvest.dms"
    When I send a "GET" request to "/dms/2"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/zip"
    Then the header "Content-Disposition" should be equal to "inline; filename=DMS_2234.zip"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.alvest.dms"
    When I send a "GET" request to "/dms/3"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "image/jpeg"
    Then the header "Content-Disposition" should be equal to "inline; filename=DMS_3234.jpg"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.alvest.dms"
    When I send a "GET" request to "/dms/4"
    Then the response status code should be 200
    Then the header "Content-Type" should be equal to "application/pdf"
    Then the header "Content-Disposition" should be equal to "inline; filename=DMS_4234.pdf"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\DMS" is exposed on the API
    Then the filter "owner" should be available and its type should be "string"
    And the filter "title" should be available and its type should be "string"
    And the filter "subject" should be available and its type should be "string"
    And the filter "description" should be available and its type should be "string"
    And the filter "language" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "portal" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"

  Scenario: Search all DMS
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms?q=subject_"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms_collection.json"

  Scenario: Search DMS list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/dms?normalization_groups_override[]=document_list&normalization_groups_override[]=expose_legacy"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/dms/schemas/dms_list.json"

  Scenario: Update a DMS should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/dms/1"
    Then the response status code should be 405

  Scenario: Create an DMS should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/dms"
    Then the response status code should be 405
