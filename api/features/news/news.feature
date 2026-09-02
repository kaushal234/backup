Feature: Test news API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\News\News" should only be available for intranet user

  Scenario: Request all news
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news/schemas/news.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\News\News" is exposed on the API
    Then the filter "order[date]" should be available and its type should be "string"
    Then the filter "order[banner]" should be available and its type should be "string"
    And the filter "legacyId" should be available and its type should be "int"
    And the filter "category" should be available and its type should be "string"
    And the filter "title" should be available and its type should be "string"
    And the filter "content" should be available and its type should be "string"
    And the filter "banner" should be available and its type should be "bool"
    And the filter "division" should be available and its type should be "string"
    And the filter "department" should be available and its type should be "string"
    And the filter "premise" should be available and its type should be "string"

  Scenario: A user can use the simple search in news
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news?q=e"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news/schemas/news.json"

  Scenario: Request a single news
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news/schemas/new.json"

  Scenario: Update a given news - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news/6" with body:
    """
    {
      "title": "this is another title"
    }
    """
    Then the response status code should be 403

  Scenario: Update a given news - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/news/6" with body:
    """
    {
      "title": "this is another title",
      "division" : "/divisions/1",
      "premise" : "/premises/3",
      "department" : "/departments/1"
    }
    """
    Then the response status code should be 200
    And the JSON node "title" should be equal to "this is another title"
    And the JSON node "division.@id" should be equal to the string "/divisions/1"
    And the JSON node "premise.@id" should be equal to the string "/premises/3"
    And the JSON node "department.@id" should be equal to the string "/departments/1"

  Scenario: Create a news - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news" with body:
    """
    {
      "title": "This is a title",
      "content": "This is a content",
      "category": "/news_categories/2"
    }
    """
    Then the response status code should be 403

  Scenario: Create a news with Banner with no banner text
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news" with body:
    """
    {
      "title": "This is a title",
      "content": "This is a content duplicated",
      "category": "/news_categories/2",
      "people": "/people/11",
      "banner": true
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "bannerText: Banner text cannot be blank if banner is checked."

  Scenario: Create a news - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news" with body:
    """
    {
      "title": "This is a title",
      "content": "This is a content",
      "category": "/news_categories/2",
      "people": "/people/11",
      "banner": true,
      "bannerText": "this is the banner text",
      "division" : "/divisions/1",
      "premise" : "/premises/3",
      "department" : "/departments/1"
    }
    """
    Then the response status code should be 201
    And the JSON node banner should be true
    And the JSON nodes should be equal to:
      | title        | This is a title          |
      | content      | This is a content        |
      | category.@id | /news_categories/2       |
      | people.@id   | /people/11               |
      | bannerText   | this is the banner text  |


  Scenario: Create a second banner should not be permitted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/news" with body:
    """
    {
      "title": "This is a title",
      "content": "This is a content duplicated",
      "category": "/news_categories/2",
      "people": "/people/11",
      "banner": true,
      "bannerText": "this is the banner text"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Only 1 banner at a time is permitted."

  Scenario: Delete a news - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/news/6"
    Then the response status code should be 403

  Scenario: Delete a news - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/news/6"
    Then the response status code should be 204

  @resetFileTable
  Scenario: Change the news file - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/news/10/picture" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 403

  Scenario: Change the news file - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/news/10/picture" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And the JSON node "id" should be equal to the number 1
    And an update log should have been inserted on resource "/news/10" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news/10"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/news/schemas/new.json"

  Scenario: Try to change the news file by an invalid file - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/news/10/picture" with file "file" "file.txt"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: This file is not a valid image"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "This file is not a valid image."

  Scenario: Delete news file - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/news/10/picture/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news/10/files/1"
    Then the response status code should be 200

  Scenario: Delete news file - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/news/10/picture/1"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news/10/files/1"
    Then the response status code should be 404

  Scenario: Email a given news
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mailer" with body:
    """
    {
        "iri": "/news/26",
        "to": ["to1@chuck.norris", "to2@chuck.norris"],
        "cc": ["cc@chuck.norris"],
        "bcc": ["bcc@chuck.norris"]
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "News Title"
    And this asynchronous email should be sent to "to1@chuck.norris"
    And this asynchronous email should be sent to "to2@chuck.norris"
    And this asynchronous email should be sent as cc to "cc@chuck.norris"
    And this asynchronous email should be sent as bcc to "bcc@chuck.norris"
    And this asynchronous email should contain "contenubizarre pas en latin"

  Scenario: Email a news and add the link
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mailer" with body:
    """
    {
        "iri": "/news/26",
        "to": ["to1@chuck.norris"],
        "link": "https://www.tld-gse.com/en/private/news/26/show"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "News Title"
    And this asynchronous email should contain 'href="https://www.tld-gse.com/en/private/news/26/show"'
    And this asynchronous email body should contain a link to "https://www.tld-gse.com/en/private/news/26/show"

  Scenario: Email a non existing news
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/mailer" with body:
    """
    {
        "iri": "/news/999",
        "to": ["to@chuck.norris"]
    }
    """
    Then the response status code should be 422
