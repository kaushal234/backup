  Feature: Test finance family module API

    Scenario: Request all finance families without being authenticated should not be permitted
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families"
      Then the response status code should be 401

    Scenario: Request a single finance family without being authenticated should not be permitted
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families/4"
      Then the response status code should be 401

    Scenario: Request all finance families as extranet user should not be permitted
      Given I authenticate as the extranet user "julien.lepers@tld.com"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families"
      Then the response status code should be 403

    Scenario: Request a finance family as extranet user should not be permitted
      Given I authenticate as the extranet user "julien.lepers@tld.com"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families/4"
      Then the response status code should be 403

    Scenario: Request all finance families as not granted authorized application
      Given I authenticate as the authorized application "La Poire Belle LN"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families"
      Then the response status code should be 403

    Scenario: Request a finance family as not granted authorized application
      Given I authenticate as the authorized application "La Poire Belle LN"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families/4"
      Then the response status code should be 403

    Scenario: Request all finance families as a vendor user
      Given I authenticate as the evendors user "vendor.user@vendor.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families"
      Then the response status code should be 403

    Scenario: Request a finance family as a vendor user should not be possible
      Given I authenticate as the evendors user "vendor.user@vendor.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families/4"
      Then the response status code should be 403

    Scenario: As a basic user I can see all finance families
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_families.json"

    Scenario: As a basic user, I can request one finance family
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      When I send a "GET" request to "/finance/finance_families/4"
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"

    Scenario: Filters are declared on resource
      Given the class "App\Entity\Finance\FinanceFamily" is exposed on the API
      Then the filter "order[id]" should be available and its type should be "string"
      And the filter "order[name]" should be available and its type should be "string"
      And the filter "archived" should be available and its type should be "bool"

    Scenario: As a basic user, I can't edit a finance family
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "PUT" request to "/finance/finance_families/4" with body:
       """
      {
        "name": "Famille Royale"
      }
      """
      Then the response status code should be 403

    Scenario: As a superuser, I can edit a finance family
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "PUT" request to "/finance/finance_families/4" with body:
       """
      {
        "name": "Famille Royale",
        "factories": ["/locations/29"]
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille Royale"

    Scenario: As CFO, I can edit a finance family
      Given I authenticate as the intranet user "user-cfo@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "PUT" request to "/finance/finance_families/4" with body:
       """
      {
        "name": "Famille Grimaldi"
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille Grimaldi"

    Scenario: As FC, I can edit a finance family
      Given I authenticate as the intranet user "user-fc@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "PUT" request to "/finance/finance_families/4" with body:
       """
      {
        "name": "Famille Adams"
      }
      """
      Then the response status code should be 200
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille Adams"

    Scenario: As a basic user, I can't create a finance family
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with the body "tests/fixtures/json/finance_family/dummies/post.json"
      Then the response status code should be 403

    Scenario: As a superuser, I can create a finance family
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with the body "tests/fixtures/json/finance_family/dummies/post.json"
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille Tuche"

    Scenario: As a superuser, I can create a finance family with same name as product family
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with body:
       """
      {
        "name": "Famille To Test Same Name Is Ok"
      }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille To Test Same Name Is Ok"

    Scenario: A family can't be created if other family exist with same name
      Given I authenticate as the intranet user "user-superuser@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with body:
       """
      {
        "name": "Famille Tuche"
      }
      """
      Then the response status code should be 422
      And the JSON node "violations[0].message" should contain "This value is already used."

    Scenario: As GTD, I can create a finance family
      Given I authenticate as the intranet user "user-gtd@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with body:
       """
      {
        "name": "Famille Ewing"
      }
      """
      Then the response status code should be 201
      And the JSON should be valid according to the schema "tests/fixtures/json/finance_family/schemas/finance_family.json"
      And the JSON node "name" should be equal to "Famille Ewing"

    Scenario: As FC, I can't create a finance family
      Given I authenticate as the intranet user "user-fc@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "POST" request to "/finance/finance_families" with body:
       """
      {
        "name": "Famille Abott"
      }
      """
      Then the response status code should be 403

    Scenario: As basic user, I can't delete a finance family
      Given I authenticate as the intranet user "user-basic@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/finance/finance_families/4"
      Then the response status code should be 403

    Scenario: As CFO, I can delete a finance family
      Given I authenticate as the intranet user "user-cfo@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/finance/finance_families/27"
      Then the response status code should be 204

    Scenario: As FC, I can delete a finance family
      Given I authenticate as the intranet user "user-fc@tld.fr"
      And I add "Accept" header equal to "application/ld+json"
      And I add "Content-Type" header equal to "application/ld+json"
      When I send a "DELETE" request to "/finance/finance_families/28"
      Then the response status code should be 204



