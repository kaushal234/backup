Feature: Test Vendor Users

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Purchasing\VendorUser" is exposed on the API
    Then the filter "id" should be available and its type should be "int"
    Then the filter "firstname" should be available and its type should be "string"
    Then the filter "lastname" should be available and its type should be "string"
    Then the filter "email" should be available and its type should be "string"
    Then the filter "erpIdentifier" should be available and its type should be "string"
    And the filter "disabled" should be available and its type should be "bool"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[firstname]" should be available and its type should be "string"
    And the filter "order[lastname]" should be available and its type should be "string"
    And the filter "order[email]" should be available and its type should be "string"
    And the filter "order[erpIdentifier]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[lastLogin]" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "lastLogin[after]" should be available and its type should be "DateTimeInterface"
    And the filter "lastLogin[before]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all vendors users without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users?itemsPerPage=2000"
    Then the response status code should be 401

  Scenario: Request all vendors users for extranet user is not allowed
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users?itemsPerPage=2000"
    Then the response status code should be 403
    And the JSON node "hydra:description" should be equal to the string "Access Denied"

  Scenario: Request all vendors users for Buyer
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/vendor_users?itemsPerPage=2000"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/vendor_user/schemas/vendor_users.json"
    And the JSON node "hydra:totalItems" should be equal to 20

  Scenario: Update vendors user should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_users/300" with body:
    """
    {
        "erpIdentifier": "125P45"
    }
    """
    Then the response status code should be 403

  Scenario: Update vendors user should be possible for buyer user
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/purchasing/vendor_users/300" with body:
    """
    {
        "erpIdentifier": "125P456"
    }
    """
    Then the response status code should be 200
    And the JSON node "erpIdentifier" should be equal to the string "125P456"
