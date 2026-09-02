Feature: Test sales demos API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\Demo" should only be available for intranet user

  Scenario: Request all demos
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demos.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Demo" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "customer" should be available and its type should be "string"
    And the filter "customer.customerTypes" should be available and its type should be "string"
    And the filter "asm" should be available and its type should be "string"
    And the filter "product" should be available and its type should be "string"
    And the filter "equipmentRecord" should be available and its type should be "string"
    And the filter "sso" should be available and its type should be "string"
    And the filter "factory" should be available and its type should be "string"
    And the filter "delinquent" should be available and its type should be "bool"
    And the filter "psm" should be available and its type should be "string"
    And the filter "expectedStartDate[strictly_before]" should be available and its type should be "DateTimeInterface"
    And the filter "country" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "order[expectedStartDate]" should be available and its type should be "string"

  Scenario: Search open demos
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos?open=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demos.json"

  Scenario: As a basic user, I can access the custom reports
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/demos;x=factory.name;y=delinquent"
    Then the response status code should be 200
    And the JSON node "yTotals.Delinquent" should exist
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/reports/resource=/sales/demos;x=sso.name;y=delinquent"
    Then the response status code should be 200
    And the JSON node "yTotals.Delinquent" should exist

  Scenario: Request a single demo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "availableStatus" should not exist

  Scenario: Request a single demo with a 'workflow' normalization group should add its available status to the response
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/1?normalizationGroups[]=workflow"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "availableStatus[0]" should be equal to the string "PENDING"
    And the JSON node "availableStatus[1]" should be equal to the string "APPROVED"
    And the JSON node "availableStatus[2]" should be equal to the string "REJECTED"

  Scenario: basic users can't update a demo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/2" with the body "tests/fixtures/json/sales/demo/dummies/put.json"
    Then the response status code should be 403

  Scenario: ASM can update a demo
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/2" with the body "tests/fixtures/json/sales/demo/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "sso.name" should be equal to the string "location_sso_2"
    And the JSON node "factory.name" should be equal to the string "location_factory_2"
    And the JSON node "asm.email" should be equal to the string "user-sa@tld.fr"
    And the JSON node "psm.email" should be equal to the string "user-sales@tld.fr"
    And the JSON node "ast.email" should be equal to the string "user-asm@tld.fr"
    And the JSON node "product.name" should be equal to the string "Produit Test"
    And the JSON node "customer.name" should be equal to the string "HELICOPT'AIR"
    And the JSON node "country.name" should be equal to the string "Kinder"
    And the JSON node "airport.code" should be equal to the string "CDG"
    And the JSON node "equipmentRecord.serialNumber" should be equal to the string "PETER"
    And the JSON node "closingComment" should be equal to the string "Voilàààà, c'est fini.."
    And the JSON node "expectedClosingStatus" should be equal to the string "SUCCESSFUL"
    And the JSON node "futureDemo.@id" should be equal to the string "/sales/demos/3"
    And the JSON node "linkAllocated" should be false
    And an update log should have been inserted on resource "/sales/demos/2" with a changeset on the property "closingComment"

  Scenario: Supervisor of demo AST can update  demo AST
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/2" with body:
    """
    {
      "ast": "/people/13"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "ast.email" should be equal to the string "user-hr@tld.fr"

  Scenario: ASM can't create a demo with expectedEndDate older than expectedStartDate
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/demos" with body:
    """
    {
      "sso": "/locations/23",
      "factory": "/locations/29",
      "asm": "/people/31",
      "psm": "/people/48",
      "ast": "/people/45",
      "product": "/sales/products/1",
      "customer": "/sales/customers/2",
      "country": "/countries/5",
      "expectedStartDate": "2017-05-03 20:10:00",
      "expectedEndDate" : "2016-05-03 10:10:00"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "endDate"
    And the JSON node "violations[0].message" should contain 'The expected end date should be greater than 2017-05-03.'

  Scenario: ASM can't update a demo with revisedEndDate older than expectedStartDate
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/2" with body:
    """
    {
      "expectedStartDate": "2017-05-03 20:10:00",
      "revisedEndDate" : "2016-05-03 10:10:00"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to "revisedEndDate"
    And the JSON node "violations[0].message" should contain "The revised end date should be greater than 2017-05-03."

  Scenario: ASM can't update a closed demo
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/5" with body:
    """
    {
      "revisedEndDate" : "2040-05-03 10:10:00"
    }
    """
    Then the response status code should be 403

  Scenario: Superuser can update a closed demo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/5" with body:
    """
    {
      "revisedEndDate" : "2040-05-03 10:10:00"
    }
    """
    Then the response status code should be 200

  Scenario: Demo is still delinquent when revised end date is set but in the past
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/6" with body:
    """
    {
      "revisedEndDate": "2017-05-03 20:10:00"
    }
    """
    Then the response status code should be 200
    And the JSON node "delinquent" should be true

  Scenario: Demo is not delinquent anymore when revised end date is set and in the future
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/6" with body:
    """
    {
      "revisedEndDate": "2099-05-03 20:10:00"
    }
    """
    Then the response status code should be 200
    And the JSON node "delinquent" should be false

  Scenario: ASM of the demo can set status to ACTIVE and Actual start date is set and email is send to status list
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/6"
    Then the response status code should be 200
    And the JSON node "activatedAt" should be null
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/6/status" with body:
    """
    {
       "status": "ACTIVE"
    }
    """
    Then the response status code should be 200
    Then an email should have been sent asynchronously with subject "M'AIR NOIRE / To be not deleted demo update"
    And this asynchronous email should be sent to "user-asm@tld.fr"
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent to "user-basic@tld.fr"
    And this asynchronous email should be sent to "user-evp@tld.fr"
    And this asynchronous email should be sent to "user-sa@tld.fr"
    And this asynchronous email should be sent to "user-coo@tld.fr"
    And this asynchronous email should be sent to "user-csd@tld.fr"
    And this asynchronous email should be sent to "user-lm@tld.fr"
    And the JSON node "status" should be equal to the string "ACTIVE"
    And the JSON node "actualStartDate" should be newer than 1 minute ago
    And the JSON node "activatedAt" should be newer than 1 minute ago

  Scenario: Superuser can't cancel a demo if status is ACTIVE
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/6/status" with body:
    """
    {
       "status": "CANCELLED"
    }
    """
    Then the response status code should be 400

  Scenario: Fields delinquent and closing date are set when demo is closed
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/4/status" with body:
    """
    {
       "status": "SUCCESSFUL"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "SUCCESSFUL"
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/4"
    Then the response status code should be 200
    And the JSON node "delinquent" should be false
    And the JSON node "closingDate" should be newer than 1 minute ago

  Scenario: Basic user can't create a demo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/demos" with the body "tests/fixtures/json/sales/demo/dummies/post.json"
    Then the response status code should be 403

  Scenario: ASM can create a demo
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/demos" with the body "tests/fixtures/json/sales/demo/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "lastCommentedAt" should be newer than 1 minute ago
    And the JSON node "linkAllocated" should be false
    Then an email should have been sent asynchronously with subject matching pattern "~SEQ #\d+: Demo application approvals~"
    And this asynchronous email should be sent only to "user-evp@tld.fr"
    And this asynchronous email should be sent as cc to "user-asm@tld.fr"

  Scenario: Superuser can create a demo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/demos" with body:
    """
    {
      "sso": "/locations/23",
      "factory": "/locations/29",
      "asm": "/people/31",
      "psm": "/people/48",
      "ast": "/people/45",
      "product": "/sales/products/1",
      "customer": "/sales/customers/2",
      "country": "/countries/5",
      "comment": "Just for test",
      "emissionRating": "/emission_ratings/5"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "lastCommentedAt" should be newer than 1 minute ago
    Then an email should have been sent asynchronously with subject matching pattern "~SEQ #\d+: Demo application approvals~"
    And this asynchronous email should be sent to "user-evp@tld.fr"
    And this asynchronous email should be sent as cc to "user-asm@tld.fr"
    And this asynchronous email should be sent as cc to "user-superuser@tld.fr"
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\S+ opened for MARTIN Anne Sophie by EVP user/"
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\S+ opened for PSM user by MARTIN Anne Sophie/"

  Scenario: ASM can't create a demo with other status than PENDING
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/demos" with the body "tests/fixtures/json/sales/demo/dummies/post_with_status.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: When ASM put a comment, Demo is not delinquent anymore
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/3" with body:
    """
    {
       "comment": "Ils m'entrainent au bout de la nuit. Qui ça, qui ça ?"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"
    And the JSON node "delinquent" should be false
    And the JSON node "lastCommentedAt" should be newer than 1 minute ago

  Scenario: Email is sent when status of the demo is set to APPROVED
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/1/status" with body:
    """
    {
       "status": "APPROVED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "APPROVED"
    Then an email should have been sent asynchronously with subject "M'AIR NOIRE / Produit Test demo update"
    And this asynchronous email should be sent to "user-ceo@tld.fr"
    And this asynchronous email should be sent to "user-evp@tld.fr"
    And this asynchronous email should be sent to "user-asm@tld.fr"
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent to "user-pse@tld.fr"
    And this asynchronous email should be sent to "user-csm@tld.fr"
    And this asynchronous email should be sent to "user-sa@tld.fr"
    And this asynchronous email should be sent as cc to "user-mpe@tld.fr"
    And this asynchronous email should not be sent to "user-lm@tld.fr"

  Scenario: Demo status must follow the workflow
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/1/status" with body:
    """
    {
       "status": "SUCCESSFUL"
    }
    """
    Then the response status code should be 400

  Scenario: Superuser can change the closing status if the demo is already closed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/5/status" with body:
    """
    {
       "status": "REJECTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "REJECTED"

  Scenario: ASM can't change the closing status if the demo is already closed
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/7/status" with body:
    """
    {
       "status": "SUCCESSFUL"
    }
    """
    Then the response status code should be 400

  Scenario: ASM can't cancel a demo
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/7/status" with body:
    """
    {
       "status": "CANCELLED"
    }
    """
    Then the response status code should be 400

  Scenario: Superuser can cancel a demo if status is different to ACTIVE
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/demos/7/status" with body:
    """
    {
       "status": "CANCELLED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "CANCELLED"

  @resetFileTable
  Scenario: As a basic user, I can't upload a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/demos/2/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a superuser, I can upload a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/demos/2/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/demos/2" with a changeset on the property "demoFiles"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/demo/schemas/demo.json"

  Scenario: Upload an invalid file to a demo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/demos/2/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "demoFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "demoFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a demo attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/2/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/1/files/1"
    Then the response status code should be 404

  Scenario: As a basic user, I can't delete a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/demos/2/files/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can delete a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/demos/1/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/demos/2/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/demos/2" with a changeset on the property "demoFiles"

  Scenario: Sales Admin can't delete a demo
    Given I authenticate as the intranet user "user-sa@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/demos/6"
    Then the response status code should be 403

  Scenario: Superusers can delete a demo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/demos/6"
    Then the response status code should be 204
