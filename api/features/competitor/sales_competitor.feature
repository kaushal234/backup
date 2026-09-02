Feature: Test competitor API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\Competitor" should only be available for intranet user

  Scenario: I can view all competitors as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitors.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\Competitor" is exposed on the API
    Then the filter "legacyId" should be available and its type should be "int"
    And the filter "name" should be available and its type should be "string"
    And the filter "productTypes[]" should be available and its type should be "string"
    And the filter "order[name]" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: I can filter competitors by normalization group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors?normalizationGroupsOverride[]=competitor_list"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitor_list.json"

  Scenario: I can search competitors
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors?q=loser"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitors.json"

  Scenario: I can view a single competitor as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitor.json"

  Scenario: I can't update a competitor as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitors/1" with the body "tests/fixtures/json/sales/competitor/dummies/put.json"
    Then the response status code should be 403

  Scenario: I can update a competitor as a Group Technical Director
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitors/1" with the body "tests/fixtures/json/sales/competitor/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "name" should be equal to "pis t'as tord 船尾"
    And the JSON node "shortDescription" should be equal to "short 船尾"
    And the JSON node "description" should be equal to "long 船尾"
    And the JSON node "url" should be equal to "http://www.you-wrong.com"

  Scenario: I can update a competitor as a Group CEO
    Given I authenticate as the intranet user "user-gceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/competitors/1" with body:
    """
    {
      "name": "Should be updated",
      "productTypes": ["/sales/product_types/1"]
    }
    """
    Then the response status code should be 200
    And the JSON node "name" should be equal to "Should be updated"
    And the JSON node "productTypes" should have 1 element
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitor.json"

  Scenario: I can't create a competitor as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitors" with the body "tests/fixtures/json/sales/competitor/dummies/post.json"
    Then the response status code should be 403

  Scenario: I can create a competitor as a Group Technical Director
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitors" with the body "tests/fixtures/json/sales/competitor/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "name" should be equal to "Compete Thor"
    And the JSON node "shortDescription" should be equal to "Si j'avais un marteau"
    And the JSON node "description" should be equal to "Je taperais le jour"
    And the JSON node "url" should be equal to "http://www.thor.com"

  Scenario: Download excel competitor reports should be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/sales/competitors?columns=id,name,shortDescription,url,productTypes"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Name | Short Description | Url | Product Types |


  @resetFileTable
  Scenario: Upload a file to competitor without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/competitors/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload a file to competitor with permission
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/competitors/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And the JSON node "id" should be equal to the number 1
    And an update log should have been inserted on resource "/sales/competitors/1" with a changeset on the property "competitorFiles"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/competitor/schemas/competitor.json"

  Scenario: Upload an invalid file to a competitor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/competitors/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "competitorFiles: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "competitorFiles"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a competitor attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors/1/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors/2/files/1"
    Then the response status code should be 404

  Scenario: Delete a file to competitor without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/1/files/1"
    Then the response status code should be 403

  Scenario: Delete a nonexistant file to competitor with permission (as GTD)
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/2/files/1"
    Then the response status code should be 404

  Scenario: Delete a file to competitor with permission (as GTD)
    Given I authenticate as the intranet user "user-gtd@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/1/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/competitors/1" with a changeset on the property "competitorFiles"
    And there should be no file matching "*000001_should-be-updated*.pdf" in upload directory

  Scenario: Basic users can't change a competitor logo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/competitors/1/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 403

  Scenario: Change photo of a competitor with permission OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/competitors/1/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Basic users can't delete a competitor logo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/1/logo/2"
    Then the response status code should be 403

  Scenario: Superusers can delete a competitor logo
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/sales/competitors/1/logo/2"
    Then the response status code should be 204
    And I add "Accept" header equal to "application/ld+json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/competitors/1"
    And the response status code should be 200
    And the JSON node "logo" should be null

  Scenario: basic users can't delete an unused competitor
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/2"
    Then the response status code should be 403

  Scenario: Delete an unused competitor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/3"
    Then the response status code should be 204

  Scenario: Delete a used competitor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/1"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The competitor 'Should be updated' is not deletable because it is used by 2 CPR (1, 2)"

  Scenario: Delete a used competitor
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/competitors/2"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "The competitor 'winner' is not deletable because it is used by 2 FCR (2, 3)"

  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test
