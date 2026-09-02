Feature: Spare Parts Request Delivery Addresses

Feature: parts / spare parts requests / delivery addresses

  Scenario: As a basic user, test that i'm allowed to access to Delivery addresses
    When I go to "/parts/spare-parts-requests/delivery_addresses"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm not allowed to access to Delivery addresses
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/delivery_addresses"
    Then I should not be on "/parts/spare-parts-requests/delivery_addresses"
    And I should see "You do not have permissions"

  @javascript
  Scenario: As a superuser, test that i access to Delivery addresses
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/parts/spare-parts-requests/delivery_addresses"
    And I wait until I see "Delivery Addresses"
    And I wait until I see "eContact"
    And I wait until I see "Lastname"
    And I wait until I see "Firstname"
    And I wait until I see "Phone"
    And I wait until I see "Address"
    And I wait until I see "Airport"
    And I wait until I see "Last Used"