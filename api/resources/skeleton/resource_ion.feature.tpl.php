Feature: Test <?= $resourceHumanName; ?> API

  Scenario: Request a single <?= $resourceHumanName; ?> without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>/TOBECHANGED"
    Then the response status code should be 401

  Scenario: <?= $resourceHumanNamePluralized; ?> should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>/TOBECHANGED"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "<?= $resourceClass; ?>" is exposed on the API
    Then the filter "TODO" should be available and its type should be "TODO"

  Scenario: Request a collection of <?= $resourceHumanNamePluralized; ?>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>"
    And a "<?= $ionResource; ?>" SOAP client has been created
    And this client has been called on the operation "<?= $ionCollectionOperation; ?>" with the following request:
    """
    {
       "ControlArea": {
           "maxNumberOfObjects": 500,
           "Filter": {
               "LogicalExpression": {
                   "logicalOperator": "and"
               }
           }
       }
    }
    """
    Then the response status code should be 200
    And the JSON node "TODO" should be equal to "TODO"
    And the JSON should be valid according to the schema "<?= $schemaBasePath; ?>/<?= $resourceCamelCase; ?>/schemas/<?= $resourceCamelCasePluralized; ?>.json"

  Scenario: Request a single <?= $resourceHumanName; ?>
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "<?= $collectionUrl; ?>/TOBECHANGED"
    Then the response status code should be 200
    And a "<?= $ionResource; ?>" SOAP client has been created
    And this client has been called on the operation "<?= $ionItemOperation; ?>" with the following request:
    """
    {
      "DataArea": {
        "<?= $ionResource; ?>": {
          "contactCode": "TOBECHANGED"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "TODO" should be equal to "TODO"
    And the JSON should be valid according to the schema "<?= $schemaBasePath; ?>/<?= $resourceCamelCase; ?>/schemas/<?= $resourceCamelCase; ?>.json"


