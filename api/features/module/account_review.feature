Feature: Test account reviews API

  Scenario: Request all account reviews of third party app
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/account_reviews?thirdPartyApp=/modules/38"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/account_reviews.json"
    And the JSON node "hydra:totalItems" should be equal to "10"

  Scenario: As an allowed user, I can add an account review
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/account_reviews" with body:
    """
    {
      "thirdPartyApp": "/modules/38",
      "countStartAdminUsers": 10,
      "countEndAdminUsers": 11,
      "adminsComment": "test adminsComment",
      "adminAccountsConfirmed": true,
      "countAppUsers": 12,
      "countStartMembers": 13,
      "countEndMembers": 14,
      "countDisabledAccounts": 15,
      "disabledAccountsComment": "test disabledAccountsComment",
      "countEnabledAccounts": 16,
      "enabledAccountsComment": "test enabledAccountsComment",
      "userAccountsConfirmed": false
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/account_review.json"
    And the JSON node "@id" should be equal to the string "/modules/third_party_app/account_reviews/11"
    And the JSON node "thirdPartyApp.@id" should be equal to the string "/extendeds/38"
    And the JSON node "countStartAdminUsers" should be equal to "10"
    And the JSON node "countEndAdminUsers" should be equal to "11"
    And the JSON node "adminsComment" should be equal to the string "test adminsComment"
    And the JSON node "countAppUsers" should be equal to "12"
    And the JSON node "countStartMembers" should be equal to "13"
    And the JSON node "countEndMembers" should be equal to "14"
    And the JSON node "countDisabledAccounts" should be equal to "15"
    And the JSON node "countEnabledAccounts" should be equal to "16"
    And the JSON node "adminAccountsConfirmed" should be true
    And the JSON node "userAccountsConfirmed" should be false
    And the JSON node "disabledAccountsComment" should be equal to the string "test disabledAccountsComment"
    And the JSON node "enabledAccountsComment" should be equal to the string "test enabledAccountsComment"

  Scenario: As a MOO, I can add an account review on my module
    Given I authenticate as the intranet user "user-moo-esr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/modules/third_party_app/account_reviews" with body:
    """
    {
      "thirdPartyApp": "/modules/38",
      "countStartAdminUsers": 10,
      "countEndAdminUsers": 11,
      "adminsComment": "test adminsComment",
      "adminAccountsConfirmed": true,
      "countAppUsers": 12,
      "countStartMembers": 13,
      "countEndMembers": 14,
      "countDisabledAccounts": 15,
      "disabledAccountsComment": "test disabledAccountsComment",
      "countEnabledAccounts": 16,
      "enabledAccountsComment": "test enabledAccountsComment",
      "userAccountsConfirmed": false
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/account_review.json"

  Scenario: Request a single account review
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/account_reviews/11"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/module_third_party_app/schemas/account_review.json"
    And the JSON node "@id" should be equal to the string "/modules/third_party_app/account_reviews/11"

  Scenario: As a main admin, I can see audits of account review
    Given I authenticate as the intranet user "tonton@david.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/38/account_reviews"
    Then the response status code should be 200

  @resetFileTable
  Scenario: As an allowed user, I can upload a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/modules/third_party_app/account_reviews/11/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Download a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/third_party_app/account_reviews/11/files/1"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "modules/third_party_app/account_reviews/11/files/1"
    Then the response status code should be 200
