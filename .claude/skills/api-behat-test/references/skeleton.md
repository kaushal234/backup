# Skeleton — API Behat feature

Replace `<domain>`, `<resource>` (singular) and `<resources>` (plural/route).

```gherkin
Feature: Test <resource> API

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\<Domain>\<Resource>" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\<Domain>\<Resource>" is exposed on the API
    Then the filter "order[name]" should be available and its type should be "string"
    And the filter "name" should be available and its type should be "string"
    # ... ONE line per filter declared on the entity (all filters)

  Scenario: Request all <resources>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/<resources>?itemsPerPage=25"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 25 element
    And the JSON should be valid according to the schema "tests/fixtures/json/<domain>/<resource>/schemas/<resources>.json"

  Scenario: Request a single <resource>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/<resources>/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/<domain>/<resource>/schemas/<resource>.json"
    And the JSON node "name" should be equal to "..."

  Scenario: Create - insufficient permissions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/<resources>" with body:
    """
    {
      "name": "POST",
      "operationalOwner": "/people/22"
    }
    """
    Then the response status code should be 403

  Scenario: Create - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/<resources>" with body:
    """
    {
      "name": "POST",
      "operationalOwner": "/people/22"
    }
    """
    Then the response status code should be 201
    # Assert ALL nodes: what was created AND default / recomputed values
    And the JSON nodes should be equal to:
      | name                 | POST       |
      | operationalOwner.@id | /people/22 |
      | status               | ENABLED    |
      # ... one line per resource field

  Scenario: Update - permissions OK
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/<resources>/20" with body:
    """
    {
      "name": "TRUC",
      "readOnlyField": "ignored"
    }
    """
    Then the response status code should be 200
    # Assert ALL nodes: modified fields AND fields that must NOT change
    And the JSON nodes should be equal to:
      | name           | TRUC            |
      | readOnlyField  | <initial value> |
      # ... one line per field

  Scenario: Update - duplicated name
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/<resources>/20" with body:
    """
    { "name": "<already taken name>" }
    """
    Then the response status code should be 409

  Scenario: Update - business rule violation
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/<resources>/5" with body:
    """
    { "status": "DISABLED" }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to '...business message...'
```

## Done checklist

1. Portal access-control scenario + 403/200-201 pair on each mutation, using
   **specific users** chosen via `acls.yaml`/`features.yaml` (see `context.md`).
2. **All filters** on the entity tested (existence + type).
3. JSON Schema validation of GET responses (item + collection).
4. POST/PUT: **inline body** (never a dummy) + assertion of **all JSON nodes** after
   the mutation (created/modified as expected, and unchanged when that is the case).
5. Business error cases (409/422) with an assertion on `hydra:description`.
6. Side effects covered when relevant: legacy table, ION, async email, bus, log
   (see the DSL in `context.md`).
7. Feature attached to a suite in `behat.yaml` with the right contexts.
8. Database prepared (`make test-reset` if fixtures/schema changed, otherwise
   `test-refresh`) then test green in Docker:
   `docker compose -p tld exec php-api vendor/bin/behat <file> --strict`.