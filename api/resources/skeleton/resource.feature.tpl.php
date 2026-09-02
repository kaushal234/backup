Feature: Test <?= $resourceHumanName; ?> API

  Scenario: <?= $resourceHumanNamePluralized; ?> should not be accessible to XU
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "<?= $resourceClass; ?>" should only be available for intranet user

  Scenario: Request all <?= $resourceHumanNamePluralized; ?>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "<?= $schemaBasePath; ?>/<?= $resourceCamelCase; ?>/schemas/<?= $resourceCamelCasePluralized; ?>.json"

  Scenario: Request a given <?= $resourceHumanName; ?>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "<?= $schemaBasePath; ?>/<?= $resourceCamelCase; ?>/schemas/<?= $resourceCamelCase; ?>.json"

  Scenario: Update a given <?= $resourceHumanName; ?> with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "<?= $collectionUrl; ?>/1" with body:
    """
    {
    }
    """
    Then the response status code should be 403

  Scenario: Update a given <?= $resourceHumanName; ?> with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "<?= $collectionUrl; ?>/1" with body:
    """
    {
    }
    """
    Then the response status code should be 200
    And the JSON node "TODO" should be equal to "TODO"

  Scenario: Create a <?= $resourceHumanName; ?> with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "<?= $collectionUrl; ?>" with body:
    """
    {
    }
    """
    Then the response status code should be 403

  Scenario: Create a <?= $resourceHumanName; ?> with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "<?= $collectionUrl; ?>" with body:
    """
    {
    }
    """
    Then the response status code should be 201
    And the JSON node "TODO" should be equal to "TODO"
    And the JSON should be valid according to the schema "<?= $schemaBasePath; ?>/<?= $resourceCamelCase; ?>/schemas/<?= $resourceCamelCase; ?>.json"

  Scenario: Delete a <?= $resourceHumanName; ?> with no permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "<?= $collectionUrl; ?>/2"
    Then the response status code should be 403

  Scenario: Delete a <?= $resourceHumanName; ?> with permission OK (superuser)
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "<?= $collectionUrl; ?>/2"
    Then the response status code should be 204

  Scenario: Only some properties of a <?= $resourceHumanName; ?> are populated when it is created
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=<?= $collectionUrl; ?>/1&method=POST"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
    ]
    """

  Scenario: Only some properties of a <?= $resourceHumanName; ?> can be edited
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=<?= $collectionUrl; ?>/1&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
    ]
    """

  Scenario: Filters are declared on resource
    Given the class "<?= $resourceClass; ?>" is exposed on the API
    Then the filter "TODO" should be available and its type should be "TODO"
