Feature: Test Supplier Ranking Files

  @resetFileTable
  Scenario: Import supplier ranking file expired
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/supplier_ranking/supplier_rankings/2/files" with parameters:
      | key       | value                                          |
      | file      | @file.xlsx                                     |
      | category  | /purchasing/supplier_ranking/file_categories/5 |
      | expiredAt | 2020-01-01 |
    Then the response status code should be 201

  Scenario: Check file previously uploaded in the list
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking_files?supplierRanking=/purchasing/supplier_ranking/supplier_rankings/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_ranking/schemas/supplier_ranking_files.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].category.id" should be equal to the string "5"
    And the JSON node "hydra:member[0].category.name" should be equal to the string "Code Ethic"
    And the JSON node "hydra:member[0].expired" should be equal to true

  Scenario: Delete a file with non authorized user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/supplier_rankings/2/files/1"
    Then the response status code should be 403

  Scenario: Delete a file
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/supplier_rankings/2/files/1"
    Then the response status code should be 204

  @resetFileTable
  Scenario: Import supplier ranking file from buyer
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/supplier_ranking/supplier_rankings/9/files" with parameters:
      | key       | value                                          |
      | file      | @file.xlsx                                     |
      | category  | /purchasing/supplier_ranking/file_categories/1 |
    Then the response status code should be 201

  Scenario: Check file is in the supplier ranking files list
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/9"
    Then the response status code should be 200
    And the JSON node "files[0].category.id" should be equal to the string "1"
    And the JSON node "files[0].expiredAt" should be null
    And the JSON node "files[0].expired" should be false

  Scenario: Buyer can delete a file
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/purchasing/supplier_ranking/supplier_rankings/9/files/1"
    Then the response status code should be 204

  Scenario: Buyer can upload a file
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/purchasing/supplier_ranking/supplier_rankings/1/files" with parameters:
      | key       | value                                          |
      | file      | @file.xlsx                                     |
      | category  | /purchasing/supplier_ranking/file_categories/1 |
    Then the response status code should be 201
  
  @resetFileTable
  Scenario: Get Supplier Rankings Files Statistics
    Given I authenticate as the intranet user "user-cpo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/supplier_ranking/supplier_rankings/statistics"
    Then the response status code should be 200
    And the JSON node "totalCompletionRate" should be equal to 0
    And the JSON node "mandatoryCompletionRate" should be equal to 0
