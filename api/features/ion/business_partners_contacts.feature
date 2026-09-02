Feature: ERP Business Partner Contacts can be fetched through the API

  Scenario: Request a single Business Partner Contact without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts/420000001"
    Then the response status code should be 401

  Scenario: Business Partner Contacts should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts/420000001"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact" is exposed on the API
    Then the filter "logicalOperator" should be available and its type should be "string"
    Then the ION filter "emailAddress" should be available and its type should be "string"

  Scenario: Request a collection of Business Partner Contacts limited to one item
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts?itemsPerPage=13"
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 13,
          "Filter": {
            "LogicalExpression": {
              "logicalOperator": "and"
            }
          }
      }
    }
    """
    And a total of 1 request has been sent to ION
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 13 element
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partner_contacts/schemas/business_partner_contacts.json"

  Scenario: Request a single Business Partner Contact as an intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts/420000001"
    Then the response status code should be 200
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Contact_v3": {
          "contactCode": "420000001"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "contactCode" should be equal to "420000001"
    And the JSON node "emailAddress" should be equal to the string "devteam@tld-america.com"
    And the JSON node "firstName" should be equal to the string "Philippe"
    And the JSON node "familyName" should be equal to the string "Last"
    And the JSON node "businessPartners" should have 3 elements
    And the JSON node "categories" should have 2 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partner_contacts/schemas/business_partner_contact.json"

  Scenario: Request a single Business Partner Contact as a wrong vendor user
    Given I authenticate as the evendors user "vendor.user2@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts/420000001"
    Then the response status code should be 403
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Contact_v3": {
          "contactCode": "420000001"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Request a single Business Partner Contact as a vendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/business_partner_contacts/420000001"
    Then the response status code should be 200
    And a "Contact_v3" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Contact_v3": {
          "contactCode": "420000001"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "contactCode" should be equal to "420000001"
    And the JSON node "emailAddress" should be equal to the string "devteam@tld-america.com"
    And the JSON node "firstName" should be equal to the string "Philippe"
    And the JSON node "familyName" should be equal to the string "Last"
    And the JSON node "businessPartners" should have 3 elements
    And the JSON node "categories" should have 2 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/business_partner_contacts/schemas/business_partner_contact.json"
