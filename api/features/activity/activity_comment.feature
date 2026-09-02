Feature: Test Activity Comment API

  Scenario: Comments should be accessible to extranet users without private comments
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/service/technician_on_calls/44"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].message" should be equal to "Public comment TOC#44"
    And the JSON node "hydra:member[0].public" should be equal to true

  Scenario: Create a comment with wrong resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with the body "tests/fixtures/json/activity_comment/dummies/post_wrong.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "resource: The resource '/foobar/1' does not exist"
    And the JSON node "violations[0].propertyPath" should be equal to "resource"
    And the JSON node "violations[0].message" should contain "The resource '/foobar/1' does not exist"

  Scenario: Create a comment with unexisting resource
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with the body "tests/fixtures/json/activity_comment/dummies/post_unexisting_resource.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "resource: The resource '/positions/999999' does not exist"
    And the JSON node "violations[0].propertyPath" should be equal to "resource"
    And the JSON node "violations[0].message" should contain "The resource '/positions/999999' does not exist"

  Scenario: Create a comment
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with the body "tests/fixtures/json/activity_comment/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_comment/schemas/comment.json"

  Scenario: Request all comments
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_comment/schemas/comments.json"

  Scenario: Request comments as an evendor user
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/materials/purchase_orders/id=120158;erp=520"
    Then the response status code should be 200

  Scenario: Request all comments with an additional normalization group
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?normalization_groups[]=manual"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_comment/schemas/comments.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Activity\Comment" is exposed on the API
    Then the filter "resource" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "discriminator" should be available and its type should be "string"
    And the filter "extraComment" should be available and its type should be "bool"

  @resetFileTable
  Scenario: As a user, I can upload a file to a comment
    Given I authenticate as the intranet user "user-mis@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key                     | value                                         |
      | file                    | @file.doc                                     |
      | resource                | /groups/1                                     |
      | message                 | test                                          |
      | fileDescription         | file description                              |
      | public                  | false                                         |
    Then the response status code should be 201
    And the JSON node "resource" should be equal to the string "/groups/1"
    And the JSON node "message" should be equal to the string "test"
    And the JSON node "public" should be false
    And the JSON node "files" should have 1 element
    And the JSON node "files[0].description" should be equal to the string "file description"


