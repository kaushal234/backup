Feature: Test sales customers type

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\CustomerType" should only be available for intranet user

  Scenario: Request all sales customer types
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_types"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_type/schemas/customer_types.json"

  Scenario: Request a single sales customer type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_types/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_type/schemas/customer_type.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\CustomerType" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "name" should be available and its type should be "string"

  Scenario: As a basic user, I can't create a customer type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/customer_types" with body:
    """
    {
      "name": "EnorméSec"
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't update a customer type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/customer_types/1" with body:
    """
    {
      "name": "EnorméSec2muscle"
    }
    """
    Then the response status code should be 403

  Scenario: As a basic user, I can't delete a customer type
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/customer_types/1"
    Then the response status code should be 403

  Scenario: As a superuser user, I can create a customer type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/customer_types" with body:
    """
    {
      "name": "KarenDestructor"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_type/schemas/customer_type.json"

  Scenario: As a superuser user, I can update a customer type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/customer_types/5" with body:
    """
    {
      "name": "KarenMinator"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer_type/schemas/customer_type.json"

  Scenario: As a superuser user, I can't update a locked customer type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/customer_types/11" with body:
    """
    {
      "name": "NoMoreMilitaryPeaceBro"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "Value 'Military' is locked and can't be updated"
    And the JSON node "violations[0].propertyPath" should be equal to "name"
    And the JSON node "violations[0].message" should contain "Value 'Military' is locked and can't be updated"

  Scenario: As a superuser user, I can't delete a customer type linked to a customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/customer_types/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Customer Type 'Top' is not deletable because it is used by 8 customer (AIR DE RIEN, AIR PES - 空气, ASM WILL BE FIRED, M'AIR NOIRE, HELICOPT'AIR, **DEMO**, customer_for_order, customer_for_mim)"

  Scenario: As a superuser user, I can delete a customer type
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "DELETE" request to "/sales/customer_types/12"
    Then the response status code should be 204
