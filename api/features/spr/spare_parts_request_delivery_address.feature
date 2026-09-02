Feature: Test Spare Parts Request Delivery Address API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Parts\SparePartsRequestDeliveryAddress" should only be available for intranet user

  Scenario: Request all Spare Parts Request Delivery Addresses
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_request_delivery_addresses?contact.extranetUserProfile.customer=/sales/customers/1&airport=/airports/61&order[lastUsedAt]=ASC&contact=/sales/extranet_users/200"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request_delivery_addresses.json"

  Scenario: Request a given Spare Parts Request Delivery Address
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/spare_parts_request_delivery_addresses/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spare_parts_request/schemas/spare_parts_request_delivery_address.json"

  Scenario: Archive and reactivate a Spare Parts Request Delivery Address
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "PATCH" request to "/parts/spare_parts_request_delivery_addresses/1/toggle_archive"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "/parts/spare_parts_request_delivery_addresses/1"
    Then the response status code should be 200
    And the JSON node "archived" should be true
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "PATCH" request to "/parts/spare_parts_request_delivery_addresses/1/toggle_archive"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "/parts/spare_parts_request_delivery_addresses/1"
    Then the response status code should be 200
    And the JSON node "archived" should be false