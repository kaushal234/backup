Feature: Test quotations API
  Scenario: Ensure the locations/1 is a SPH
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/locations/1" with the body "tests/fixtures/json/directory_location/dummies/put.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\SPQ\Quotation" should only be available for intranet user

  Scenario: Request all quotations
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation/schemas/quotations.json"

  Scenario: Request all quotations with totals
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations?normalization_groups[]=quotation_totals"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation/schemas/quotations_totals.json"
    And the JSON node "hydra:totalItems" should be equal to 32
    And less than 15 database queries must have been executed

  Scenario: Quotation must be available in CSV
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/parts/quotations?normalization_groups[]=quotation_totals&properties[]=id&properties[]=createdAt&properties[]=receivedAt&properties[]=suspendedAt&properties[]=expiredAt&properties[]=closedAt&properties[]=firstSubmittedAt&properties[]=submittedAt&properties[poster][]=firstname&properties[poster][]=lastname&properties[source][]=firstname&properties[source][]=lastname&properties[quoter][]=firstname&properties[quoter][]=lastname&properties[sph][]=name&properties[sph][]=erp&properties[]=rfq&properties[]=baanCustomerNumber&properties[]=baanCustomerName&properties[]=baanSalesOrder&properties[]=customerPurchaseOrder&properties[]=status&properties[requestType][]=name&properties[]=customerName&properties[]=contactEmails&properties[]=currency&properties[]=reason"
    Then the response status code should be 200
    And the csv file headers are:
      | id | createdAt | receivedAt | suspendedAt | expiredAt | closedAt | submittedAt | firstSubmittedAt | poster.firstname | poster.lastname | source.firstname | source.lastname | quoter.firstname | quoter.lastname | sph.name | sph.erp | rfq | baanCustomerNumber | baanCustomerName | baanSalesOrder | customerPurchaseOrder | status | requestType.name | customerName | contactEmails | currency | reason |
    And the CSV file should have 33 lines
    And less than 15 database queries must have been executed

  Scenario: Request a quotation with timezone
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations/1?normalization_groups[]=location_detail"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation/schemas/quotation_location.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\SPQ\Quotation" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    And the filter "customerPurchaseOrder" should be available and its type should be "string"
    And the filter "contactEmails" should be available and its type should be "string"
    And the filter "customerName" should be available and its type should be "string"
    And the filter "baanSalesOrder" should be available and its type should be "int"
    And the filter "baanCustomerNumber" should be available and its type should be "string"
    And the filter "rfq" should be available and its type should be "string"
    And the filter "sph" should be available and its type should be "string"
    And the filter "poster" should be available and its type should be "string"
    And the filter "source" should be available and its type should be "string"
    And the filter "quoter" should be available and its type should be "string"
    And the filter "quotationLines.partNumber" should be available and its type should be "string"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "receivedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "expiredAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "submittedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "suspendedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "payableService" should be available and its type should be "bool"

  Scenario: Search all quotations with context filter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations?context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation/schemas/quotations.json"


  Scenario: Request a single quotation
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation/schemas/quotation.json"

  Scenario: Request a quotation that does not exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotations/99999"
    Then the response status code should be 404

  Scenario: Update a quotation should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/quotations/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Create a quotation should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/quotations" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Get reports for quotations matrix
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/parts/quotations;x=sph.name;y=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
