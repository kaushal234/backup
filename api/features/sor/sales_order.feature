Feature: Test Sales Order can be created and updated
  Scenario: Request all sales orders without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders"
    Then the response status code should be 401

  Scenario: Request a single sales order without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 401

  Scenario: Sales orders should not be accessible to XU nor basic user
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 403

  Scenario: Sales orders should be accessible to sales admin
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/orders.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Order" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    And the filter "endUser" should be available and its type should be "string"
    And the filter "buyer" should be available and its type should be "string"
    And the filter "sso" should be available and its type should be "string"
    And the filter "asm" should be available and its type should be "string"
    And the filter "juridicalLocation" should be available and its type should be "string"
    And the filter "salesAgent" should be available and its type should be "string"
    And the filter "inforLnBusinessPartnerCode" should be available and its type should be "string"
    And the filter "baanOrderNumbers" should be available and its type should be "string"
    And the filter "customerPurchaseOrders" should be available and its type should be "string"
    And the filter "equoteId" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "newCustomer" should be available and its type should be "bool"
    And the filter "enteredAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "order[enteredAt]" should be available and its type should be "string"

  Scenario: Sales orders should be filterable
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders?context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/orders.json"

  Scenario: Get reports for orders by sso name
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/orders;x=sso.name;y=status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/report/schemas/report.json"
    And the JSON node "total" should be equal to the number 40

  Scenario: Sales order's detail should be accessible to sales admin
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"

  Scenario: Sales order's detail should be duplicable
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders/2/duplicate" with body:
    """
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "enteredAt" should contain today's date
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\d+ opened for CFO user by CFO user/"
    And this asynchronous email body should contain a link to "https://www.tld-gse.com/en/private/sales/customers/35/show"
    And this asynchronous email should contain "CUSTOMER NAME: customer_35 (#35)"

  Scenario: Delete a sales order with attached lines allowing it
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/2"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/2"
    Then the response status code should be 204

  Scenario: Create a sales order should trigger an email to the ASM
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders" with the body "tests/fixtures/json/sales/orders/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "enteredAt" should contain today's date
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\d+ opened for MARTINE Anne Sophie by SAM user/"
    And this asynchronous email body should contain a link to "https://www.tld-gse.com/en/private/sales/customers/32/show"
    And this asynchronous email should contain "CUSTOMER NAME: ASM WILL BE FIRED (#32)"

  Scenario: Create a sales order without specified buyer should set the end user as buyer
    # moreover the /locations/31 has no erp in BAAN and therefore, there's no need for a baanCustomerNumber
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders" with body:
    """
    {
      "sso": "/locations/31",
      "juridicalLocation": "/juridical_locations/3",
      "asm": "/people/31",
      "endUser": "/sales/customers/32",
      "customerPurchaseOrders": ["foo"],
      "contact": "sales/extranet_users/242"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "buyer.@id" should be equal to the string "/sales/customers/32"
    And the JSON node "contact.@id" should be equal to the string "/sales/extranet_users/242"

  Scenario: Create an invalid sales order without should trigger business validation rules
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/orders" with body:
    """
    {
      "sso": "/locations/23",
      "juridicalLocation": "/juridical_locations/3",
      "asm": "/people/31",
      "endUser": "/sales/customers/32"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "inforLnBusinessPartnerCode"
    And the JSON node "violations[0].message" should contain "This value should not be null for the SSO location_sso."
    And the JSON node "violations[1].propertyPath" should be equal to "juridicalLocation"
    And the JSON node "violations[1].message" should contain "This value is not a valid juridical location for the SSO location_sso."
    And no email should have been sent asynchronously

  Scenario: Update a sales order
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/1" with the body "tests/fixtures/json/sales/orders/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: A sales order can be forwarded to IN PROGRESS
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/1/status" with body:
    """
    {
      "status": "IN PROGRESS"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "status" should be equal to the string "IN PROGRESS"
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/1/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response should be an error stating "Status CLOSED is not allowed. Reasons: Some linked SOLs are not closed."

  Scenario: A sales order can be pushed to CLOSED
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/orders/6/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"
    And the JSON node "closedAt" should contain today's date

  Scenario: ASM can edit their own sales order if status is pending
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/orders/6" with body:
    """
      {
        "customerPurchaseOrders": ["FOO", "BAR"]
      }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/orders/5" with body:
    """
      {
        "customerPurchaseOrders": ["FOO", "BAR"]
      }
    """
    Then the response status code should be 200
    And the JSON node "customerPurchaseOrders[0]" should be equal to the string "FOO"
    And the JSON node "customerPurchaseOrders[1]" should be equal to the string "BAR"
    And an update log should have been inserted on resource "/sales/orders/5" with a changeset on the property "customerPurchaseOrders" with values "b,c", "FOO,BAR"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "sales/orders/5" with body:
    """
      {
        "asm": "/people/56"
      }
    """
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/sales/orders/5"
    And the JSON node "asm.@id" should be equal to the string "/people/56"


  Scenario: Upload a file to an order without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/orders/1/files" with file "file" "file.pdf"
    Then the response status code should be 403
    And no email should have been sent asynchronously

  @resetFileTable
  Scenario: Upload a file to an order with permission
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/orders/1/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/orders/1" with a changeset on the property "files"
    # Check that the item schema is still valid
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 200
    And the JSON node "files" should have 1 element
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/orders/schemas/order.json"

  Scenario: As an SA, I can update description of a sales order file
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "description": "des scriptions"
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    Then the response status code should be 200
    And the JSON node "files[0].description" should be equal to the string "des scriptions"

  Scenario: As an basic user, I can not update description of a sales order file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "description": "des scriptions 2"
    }
    """
    Then the response status code should be 403

  Scenario: Upload an invalid file to an order
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/orders/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"
    And no email should have been sent asynchronously

  Scenario: Download an order file without permission should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1/files/1"
    Then the response status code should be 403

  Scenario: Download an order file
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/2/files/1"
    Then the response status code should be 404

  Scenario: Delete an order file without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/1/files/1"
    Then the response status code should be 403

  Scenario: Delete an order file with permission
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/2/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/1/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/orders/1" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/1"
    And the JSON node "files" should have 0 element

  Scenario: Delete a used Order
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/orders/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The Sales Order Line '18894' is not deletable because it is used by 1 SOL (18818)"

  @resetFileTable
  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test
