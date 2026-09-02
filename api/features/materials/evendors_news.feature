Feature: Test Evendors News API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Materials\EvendorsNews" is exposed on the API
    Then the filter "factories.erp" should be available and its type should be "int"
    And the filter "order[publishedAt]" should be available and its type should be "string"
    And the filter "publishedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "publishedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "unpublishedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "unpublishedAt[before]" should be available and its type should be "DateTimeInterface"

  Scenario: Request all Evendors News
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_news.json"

  Scenario: Request a given Evendors News
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: Update a given Evendors News with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/evendors_news/1" with body:
    """
    {
      "content": "new content",
    }
    """
    Then the response status code should be 403

  Scenario: Update a given Evendors News with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/evendors_news/1" with body:
    """
    {
      "content": "new content",
      "publishedAt": "2022-11-02T13:58:00-0400",
      "unpublishedAt": "2099-12-09T13:58:00-0500",
      "createdBy": "/people/11",
      "factories": [
        "/locations/29"
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "content" should be equal to "new content"

  Scenario: Create a Evendors News with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/evendors_news" with body:
    """
    {
      "content": "content",
    }
    """
    Then the response status code should be 403

  Scenario: Create a Evendors News with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/evendors_news" with body:
    """
    {
      "content": "new news",
      "publishedAt": "2022-11-02T13:58:00-0400",
      "unpublishedAt": "2099-12-09T13:58:00-0500",
      "createdBy": "/people/11",
      "factories": [
        "/locations/29"
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "content" should be equal to "new news"
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: Delete a Evendors News with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/evendors_news/2"
    Then the response status code should be 403

  Scenario: Delete a Evendors News with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/evendors_news/2"
    Then the response status code should be 204

  Scenario: Request all Evendors News for evendors as an evendor
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_news.json"

  Scenario: Create a Evendors News for ERP 500
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/evendors_news" with body:
    """
    {
      "content": "new news 500",
      "publishedAt": "2022-11-02T13:58:00-0400",
      "unpublishedAt": "2099-12-09T13:58:00-0500",
      "createdBy": "/people/11",
      "factories": [
        "/locations/30"
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "content" should be equal to "new news 500"
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: Create a Evendors News for ERP 600 which not readable for vendor.user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/evendors_news" with body:
    """
    {
      "content": "new news 600",
      "publishedAt": "2022-11-02T13:58:00-0400",
      "unpublishedAt": "2099-12-09T13:58:00-0500",
      "createdBy": "/people/11",
      "factories": [
        "/locations/22"
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "content" should be equal to "new news 600"
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: Vendor user can see news from ERP 500 and 540
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 3 element
    And the JSON node "hydra:member[0].content" should be equal to the string "new content"
    And the JSON node "hydra:member[1].content" should be equal to the string "new news"
    And the JSON node "hydra:member[2].content" should be equal to the string "new news 500"

  Scenario: Vendor user cannot read a news with ERP 600
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news/33"
    Then the response status code should be 404

  Scenario: Update last news with good ERP for vendor.user
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/evendors_news/33" with body:
    """
    {
      "factories": [
        "/locations/22",
        "/locations/30"
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: And now Vendor user can see news from ERP 500 and 540 and 600
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 4 element
    And the JSON node "hydra:member[3].content" should be equal to the string "new news 600"

  Scenario: Update last news with expired date of publication
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/evendors_news/33" with body:
    """
    {
      "unpublishedAt": "2022-11-03T13:58:00-0400"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/evendors_news/schemas/evendors_new.json"

  Scenario: And now Vendor user cannot see the last updated news
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 3 element

  @resetFileTable
  Scenario: Upload an attached file to evendors news 1
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/evendors_news/1/files" with parameters:
      | key    | value     |
      | file   | @file.pdf |
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "/evendors_news/1"
    And the JSON node "files" should have 1 element

  Scenario: intranet_user want to know how many file on evendors news 1
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "/evendors_news/1"
    And the JSON node "files" should have 1 element

  Scenario: And now vendor user see attached files to evendors news 1
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/evendors_news/1"
    Then the response status code should be 200
    And the JSON node "files" should have 1 element

  Scenario: delete an attached file on evendors news
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "DELETE" request to "/evendors_news/1/files/1"
    And I add "Accept" header equal to "application/ld+json"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I send a "GET" request to "/evendors_news/1"
    And the JSON node "files" should have 0 element

  @resetFileTable
  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test
