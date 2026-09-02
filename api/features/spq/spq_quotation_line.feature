Feature: Test quotation lines API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\SPQ\QuotationLine" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\SPQ\QuotationLine" is exposed on the API
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "quotation.payableService" should be available and its type should be "bool"
    And the filter "quotation" should be available and its type should be "string"
    And the filter "partNumber" should be available and its type should be "string"
    And the filter "quotation.sph" should be available and its type should be "string"
    And the filter "quotation.status" should be available and its type should be "string"
    And the filter "quotation.poster" should be available and its type should be "string"
    And the filter "quotation.quoter" should be available and its type should be "string"
    And the filter "quotation.baanCustomerNumber" should be available and its type should be "string"
    And the filter "quotation.closedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all quotation lines ordered by creation date with context filter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotation_lines?context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation_line/schemas/quotation_lines.json"

  Scenario: Request all quotation lines
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotation_lines"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation_line/schemas/quotation_lines.json"

  Scenario: Request a single quotation line
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotation_lines/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation_line/schemas/quotation_line.json"

  Scenario: Request a quotation line that does not exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotation_lines/99999"
    Then the response status code should be 404

  Scenario: Update a quotation line should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/quotation_lines/1" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: Create a quotation line should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/quotation_lines" with body:
    """
    {}
    """
    Then the response status code should be 405

  Scenario: I can't delete a quotation line
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/quotation_lines/111"
    Then the response status code should be 405

  Scenario: Extended information can be retrieved for report using properties filter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/quotation_lines?normalization_groups[]=quotation_lines:reports&normalization_groups[]=people_list&normalization_groups[]=location_public&properties[]=id&properties[]=status&properties[]=partNumber&properties[]=description&properties[]=unitBasePrice&properties[]=quantity&properties[]=salesUnit&properties[]=currency&properties[]=discount&properties[]=totalPrice&properties[]=commission&properties[]=createdAt&properties[quotation][]=id&properties[quotation][]=status&properties[quotation][]=baanCustomerNumber&properties[quotation][sph][]=name&properties[quotation][sph][]=erp&properties[quotation][]=customerPurchaseOrder&properties[quotation][]=baanSalesOrder&properties[quotation][]=baanCustomerName&properties[quotation][quoter][]=firstname&properties[quotation][quoter][]=lastname&properties[quotation][]=reason&properties[quotation][]=payableService"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/spq/quotation_line/schemas/quotation_lines_extended.json"

  Scenario: User can get CSV reports from quotation lines
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "text/csv"
    When I send a "GET" request to "/parts/quotation_lines?normalization_groups[]=quotation_lines:reports&normalization_groups[]=people_list&normalization_groups[]=location_public&properties[]=id&properties[]=status&properties[]=partNumber&properties[]=description&properties[]=unitBasePrice&properties[]=quantity&properties[]=salesUnit&properties[]=currency&properties[]=discount&properties[]=totalPrice&properties[]=commission&properties[]=createdAt&properties[quotation][]=id&properties[quotation][]=status&properties[quotation][]=baanCustomerNumber&properties[quotation][sph][]=name&properties[quotation][sph][]=erp&properties[quotation][]=customerPurchaseOrder&properties[quotation][]=baanSalesOrder&properties[quotation][]=baanCustomerName&properties[quotation][quoter][]=firstname&properties[quotation][quoter][]=lastname&properties[quotation][]=reason&properties[quotation][]=payableService"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=utf-8"
    And the csv file headers are:
      | id | status | partNumber | description | unitBasePrice | totalPrice | currency | quantity | salesUnit | discount | commission | createdAt | quotation.id | quotation.quoter.firstname | quotation.quoter.lastname | quotation.sph.name | quotation.sph.erp | quotation.baanCustomerNumber | quotation.baanCustomerName | quotation.baanSalesOrder | quotation.customerPurchaseOrder | quotation.status | quotation.payableService | quotation.reason |
    And the csv file should have 118 lines
