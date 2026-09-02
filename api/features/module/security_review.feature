Feature: Test security reviews API
  Scenario: Request all security reviews of third party app
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/security_reviews?thirdPartyApp=/modules/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_reviews.json"
    And the JSON node "hydra:totalItems" should be equal to "10"

  Scenario: As an allowed user, I can add an security review
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/security_reviews" with body:
    """
    {
      "thirdPartyApp": "/modules/38",
      "securityLevelComment": "test securityLevelComment",
      "isPasswordPolicyApplied": true,
      "isMFAAdminApplied":false,
      "isMFAUserApplied": true,
      "securityLevel": "/security_levels/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_review.json"
    And the JSON node "@id" should be equal to the string "/modules/third_party_app/security_reviews/11"
    And the JSON node "thirdPartyApp.@id" should be equal to the string "/extendeds/38"
    And the JSON node "securityLevelComment" should be equal to the string "test securityLevelComment"
    And the JSON node "isPasswordPolicyApplied" should be true
    And the JSON node "isMFAAdminApplied" should be false
    And the JSON node "isMFAUserApplied" should be true
    And the JSON node "securityLevel.@id" should be equal to the string "/security_levels/1"

  Scenario: As a MOO, I can add a security review on my module
    Given I authenticate as the intranet user "user-moo-esr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/security_reviews" with body:
    """
    {
      "thirdPartyApp": "/modules/38",
      "securityLevelComment": "test securityLevelComment",
      "isPasswordPolicyApplied": true,
      "isMFAAdminApplied":false,
      "isMFAUserApplied": true,
      "securityLevel": "/security_levels/1"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_review.json"

  Scenario: Request a single security review
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/security_reviews/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/security_review.json"
    And the JSON node "@id" should be equal to the string "/modules/third_party_app/security_reviews/11"

  Scenario: As a main admin, I can see audits of security review
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/38/security_reviews"
    Then the response status code should be 200

  @resetFileTable
  Scenario: As an allowed user, I can upload a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/modules/third_party_app/security_reviews/11/files" with parameters:
      | key             | value                                         |
      | public          | 0                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Download a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/security_reviews/11/files/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/security_reviews/11/files/1"
    Then the response status code should be 200
