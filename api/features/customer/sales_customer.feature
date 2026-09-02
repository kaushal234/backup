Feature: Test sales customers API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\Customer" should only be available for intranet user

  Scenario: Request all sales customers
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Customer" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "hidden" should be available and its type should be "bool"
    And the filter "exists[mainSalesRepresentative]" should be available and its type should be "bool"
    And the filter "country" should be available and its type should be "string"
    And the filter "crt.partsLocation" should be available and its type should be "string"
    And the filter "crt.serviceLocation" should be available and its type should be "string"
    And the filter "crt.erpLocation" should be available and its type should be "string"
    And the filter "crt.acls.extranetUserGroup" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "watchList" should be available and its type should be "bool"
    And the filter "inforLnBusinessPartnerCodes" should be available and its type should be "string"

  Scenario: Request sales customers ordered by name using context filter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?context[no_headers]=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers.json"

  Scenario: Request sales customers active
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?active=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers.json"
    And the JSON node "hydra:totalItems" should be equal to 45

  Scenario: Request sales customers with CRT
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?has_crt=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers.json"
    And the JSON node "hydra:totalItems" should be equal to 10

  Scenario: Search sales customers on partial name
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?q=rien"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers.json"

  Scenario: Request all sales customers for a select list
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?normalization_groups_override[]=customer_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers_list.json"

  Scenario: Request all sales customers for an export should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers_export"
    Then the response status code should be 403

  Scenario: Request all sales customers for an export should be possible for TLD GROUP user
    Given I authenticate as the intranet user "user-gcoo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers_export"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customers_export.json"

  Scenario: Request all sales customers for a select list with CRT
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?normalization_groups_override[]=customer_crt&normalization_groups_override[]=customer_list"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer_list_with_crt.json"

  Scenario: Request a single sales customer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"

  Scenario: Request a single sales customer should be possible only for ION application and should only display status
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1/status"
    Then the response status code should be 403
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1/status"
    Then the response status code should be 403
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1/status"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer_status.json"

  Scenario: Request a single sales customer wit CRTs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1?normalization_groups[]=customer_crt"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer_with_crt.json"

  Scenario: Request sales customers with watch list information
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers?normalization_groups[]=customer:watch"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer_list_with_watch_list.json"

  Scenario: basic users can't update a sales customer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with the body "tests/fixtures/json/sales/customer/dummies/put.json"
    Then the response status code should be 403

  Scenario: ASM can't update a sales customer status only admin or sam can
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/status" with body:
    """
    {
      "status": "APPROVED"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1/status" with body:
     """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "status" should be equal to "PENDING"
    And an email should have been sent asynchronously with subject matching pattern "/SEQ#\S+: Initiation of customer validation sequence/"
    And this asynchronous email should be sent only to "user-superuser@tld.fr"
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1/status" with body:
     """
    {
      "status": "APPROVED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "status" should be equal to "APPROVED"

  Scenario: ASM can update a sales customer but can't update hidden property
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with the body "tests/fixtures/json/sales/customer/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "hidden" should be false
    And an update log should have been inserted on resource "/sales/customers/2" with a changeset on the property "name"

  Scenario: Update a sales customer should generate a notification is sent
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/33" with body:
    """
    {
      "country": "/countries/3"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And an email should have been sent asynchronously with subject matching pattern "/Customer M'AIR NOIRE \(ID#\S+\) edition/"
    And this asynchronous email should be sent only to "user-parts@tld.fr, user-sales@tld.fr"

  Scenario: Update customer main contact should update CRT Sales representative
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/36" with body:
    """
    {
      "mainSalesRepresentative": {
        "asm": "/people/31",
        "subDivision": "/sub_divisions/1"
      }
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And a message of class "App\Message\Sales\CustomerMainRepresentativeUpdate" should have been sent in the bus


  Scenario: Customer can't be its own parent
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "parentCustomer": "/sales/customers/2"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "A customer cannot be its own parent."

  Scenario: Customer can't be its own parent
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1" with body:
    """
    {
      "parentCustomer": "/sales/customers/34"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should contain "HELICOPT'AIR cannot be the parent of because it is in the hierarchy of the children of this customer (child of M'AIR NOIRE)."

  Scenario: ASM or EVP can't update customer credit limit, but user CFO can (only if credit limit type doesn't already exists on customer)
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "creditLimits": [
        {
          "type": "FACTOR",
          "amount": 150000,
          "currency": "/finance/currencies/2"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "creditLimits" should have 0 element
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "creditLimits": [
        {
          "type": "FACTOR",
          "amount": 150000,
          "currency": "/finance/currencies/2"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "creditLimits" should have 0 element
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "name": "toto test",
      "creditLimits": [
        {
          "type": "FACTOR",
          "amount": 150000,
          "currency": "/finance/currencies/2"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "creditLimits" should have 1 element
    And the JSON node "name" should not be equal to the string "toto test"
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "creditLimits": [
        {
          "type": "FACTOR",
          "amount": 150000,
          "currency": "/finance/currencies/1"
        }
      ]
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "This credit limit type is already defined for this eCustomer."

  Scenario: user sam can update a sales customer status
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/status" with body:
     """
    {
      "status": "APPROVED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "status" should be equal to "APPROVED"

  Scenario: user sam can put customer as hidden
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2" with body:
    """
    {
      "hidden": true
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "hidden" should be true

  Scenario: Basic user can't create a sales customer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customers" with the body "tests/fixtures/json/sales/customer/dummies/post.json"
    Then the response status code should be 403

  Scenario: SA can create a sales customer
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customers" with the body "tests/fixtures/json/sales/customer/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"
    And the JSON node "status" should be equal to "APPROVED"
    And an email should have been sent asynchronously with subject matching pattern "/SEQ#\S+ requires your attention \(New eCustomer AIR PES\)/"
    And this asynchronous email should be sent only to "user-sa@tld.fr"

  Scenario: Basic users can get top parent of a customer
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/hierarchy"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to "/sales/customers/1/hierarchy"
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"

  Scenario: As an ASM, I can update description of a customer file of a customer I own
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/8" with body:
    """
    {
      "description": "Kikou les p'tites beautés"
    }
    """
    Then the response status code should be 204
    And the response should be empty
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/customer_files/8"
    Then the response status code should be 200
    And the JSON node "description" should be equal to "Kikou les p'tites beautés"

  Scenario: As an ASM, I can't update description of a customer file of a customer I don't own
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/9" with body:
    """
    {
      "description": "Denied genius"
    }
    """
    Then the response status code should be 403

  Scenario: As an Superuser, I can update description of a customer file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/8" with body:
    """
    {
      "description": "Sivouplé"
    }
    """
    Then the response status code should be 204
    And the response should be empty
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/customer_files/8"
    Then the response status code should be 200
    And the JSON node "description" should be equal to "Sivouplé"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\CustomerFile" is exposed on the API
    Then the filter "description" should be available and its type should be "string"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "subDivision.name" should be available and its type should be "string"
    And the filter "subDivision" should be available and its type should be "string"

  @resetFileTable
  Scenario: Upload a file to customer without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/customers/33/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload a file to customer with permission
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/customers/33/files" with parameters:
      | key             | value                                         |
      | description     | blabla                                        |
      | subDivision     | /sub_divisions/3                              |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/customers/33" with a changeset on the property "customerFiles"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/customer/schemas/customer.json"

  Scenario: Upload an invalid file to a customer
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/customers/33/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "customerFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "customerFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a customer attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 403

  Scenario: Download all customer attached files as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/sales/customers/33/files"
    Then the response status code should be 403

  Scenario: Download a customer attached file as the ASM of the customer
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Download a customer attached file as the FC of the customer
    Given I authenticate as the intranet user "user-fc@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Download a customer attached file as the CFO of the customer
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Download a customer attached file as secondary ASM of the customer
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Download a customer attached file as the supervisor of the ASM of the customer
    Given I authenticate as the intranet user "user-parts@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Download all customer attached files as the ASM of the customer
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/sales/customers/33/files"
    Then the response status code should be 200
    And the header "Content-type" should be equal to "application/zip"

  Scenario: Download zip files when no files as superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/zip"
    When I send a "GET" request to "/sales/customers/34/files"
    Then the response status code should be 204

  Scenario: Download all customer attached files is not possible if json
    Given I authenticate as the intranet user "user-sales@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files"
    Then the response status code should be 406

  Scenario: Download a customer attached file as the ceo related to the location of the ASM of the customer
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/33/files/1"
    Then the response status code should be 200

  Scenario: Delete a file to customer without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/33/files/1"
    Then the response status code should be 403

  Scenario: Delete a file to customer with permission (as MOO of ECUST module)
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/33/files/1000"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/33/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/customers/33" with a changeset on the property "customerFiles"

  Scenario: Basic users can't change a customer logo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/customers/1/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 403

  Scenario: Change photo of a customer with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/customers/1/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Basic users can't delete a customer logo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/1/logo/2"
    Then the response status code should be 403

  Scenario: Superusers can delete a customer logo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/sales/customers/1/logo/2"
    Then the response status code should be 204
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/1"
    And the response status code should be 200
    And the JSON node "logo" should be null

  Scenario: Superusers can delete a customer if not used by CRT, ER, Demo, or SFR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/20"
    Then the response status code should be 204

  Scenario: User Sa can delete a customer if not used by CRT, ER, Demo, or SFR
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/21"
    Then the response status code should be 204

  Scenario: Superusers can't delete a customer if used by CRT
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer 'AIR DE RIEN' is not deletable because it is used by 2 CRT (1, 7)"

  Scenario: Superusers can't delete a customer if used by SFR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/42"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer 'customer_very_very_nested' is not deletable because it is used by 1 SFR (2)"

  Scenario: Superusers can't delete a customer if used by ER
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/31"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer 'customer_for_er' is not deletable because it is used by 2 ER (THOMAS, polo)"

  Scenario: Superusers can't delete a customer if used by Order
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/44"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer 'customer_for_order' is not deletable because it is used by 1 SOR (6)"

  Scenario: Superusers can't delete a customer if used by Demo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/43"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer '**DEMO**' is not deletable because it is used by 1 Demo (7)"

  Scenario: Superusers can't delete a customer if used by AR
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/customers/32"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The customer 'ASM WILL BE FIRED' is not deletable because it is used by 3 AR (1, 2, 3)"

  Scenario: ASM can't put customer on watch list
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/watch_list" with body:
    """
    {
      "watchList": true,
      "watchListReason": "don't pay the bills"
    }
    """
    Then the response status code should be 403

  Scenario: Reason is mandatory when a customer is put on the watch list
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/watch_list" with body:
    """
    {
      "watchList": true
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "watchListReason: You must give a reason when you put a customer on the watchlist"
    And the JSON node "violations[0].propertyPath" should be equal to "watchListReason"
    And the JSON node "violations[0].message" should contain "You must give a reason when you put a customer on the watchlist"

  Scenario: EVP can put customer on watch list
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/watch_list" with body:
    """
    {
      "watchList": true,
      "watchListReason": "don't pay the bills"
    }
    """
    Then the response status code should be 200
    And the JSON node "watchList" should be true

  Scenario: Reason is not mandatory when a customer is put off the watch list
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/2/watch_list" with body:
    """
    {
      "watchList": false
    }
    """
    Then the response status code should be 200
    And the JSON node "watchList" should be false

  Scenario: Deactivate a customer that was approved should not be authorized as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/28/status" with body:
    """
    {
      "status": "NOT ACTIVE"
    }
    """
    Then the response status code should be 403

  Scenario: Deactivate a customer that was approved, as a user-superuser
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/28/status" with body:
    """
    {
      "status": "NOT ACTIVE"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "NOT ACTIVE"
