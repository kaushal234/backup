Feature: Test tracking

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Parts\Tracking" should only be available for intranet user

  Scenario: Request all trackings
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/trackings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/tracking/schemas/trackings.json"

  Scenario: Request a single tracking
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/trackings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/tracking/schemas/tracking.json"
    And the JSON node "link" should be equal to the string "https://allez.bougezaveclaposte.com/traquing?lang=wet&numerodecolis=THISISATRACKINGNUMBER"

  Scenario: POST is not supported anymore on Trackings
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/trackings" with body:
    """
    {
    }
    """
    Then the response status code should be 405

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Parts\Tracking" is exposed on the API
    And the filter "location" should be available and its type should be "string"
    And the filter "packingSlip" should be available and its type should be "int"
    And the filter "trackingNumber" should be available and its type should be "string"
    And the filter "salesOrder" should be available and its type should be "string"
    And the filter "deliveryDate[after]" should be available and its type should be "DateTimeInterface"
    And the filter "deliveryDate[before]" should be available and its type should be "DateTimeInterface"

  Scenario: File upload is not supported anymore
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/trackings/1/files" with file "file" "file.doc"
    Then the response status code should be 404

  Scenario: File deletion is not supported anymore
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/trackings/1/files/1"
    Then the response status code should be 405
