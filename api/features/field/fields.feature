Feature: Test denormalized fields exposure depending on context

  Scenario: MOO can edit all Sales Forecast fields
    Given I authenticate as the intranet user "user-moo-sfr@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/sales/sales_forecasts/13&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
        "masterSalesForecast",
        "status",
        "sso",
        "factory",
        "asm",
        "equoteId",
        "buyer",
        "endUser",
        "thirdParty",
        "country",
        "airport",
        "product",
        "quantity",
        "estimatedSaleDate",
        "customerSuccessPercentage",
        "successPercentage",
        "tier",
        "price",
        "margin",
        "comment",
        "synchronized",
        "notificationRestricted",
        "notifyPackage",
        "quote"
    ]
    """

  Scenario: User ASM can only edit some Sales Forecast fields
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "fields?iri=/sales/sales_forecasts/13&method=PUT"
    Then the response status code should be 200
    Then the JSON should be equal to:
    """
    [
        "masterSalesForecast",
        "status",
        "sso",
        "equoteId",
        "buyer",
        "endUser",
        "thirdParty",
        "country",
        "airport",
        "product",
        "quantity",
        "estimatedSaleDate",
        "customerSuccessPercentage",
        "successPercentage",
        "tier",
        "price",
        "margin",
        "comment",
        "synchronized",
        "notificationRestricted",
        "notifyPackage",
        "quote"
    ]
    """

  Scenario: iri and method parameters are mandatory
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields"
    Then the response status code should be 400

  Scenario: A PSM has a special denormalization context for SFR
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/sales/sales_forecasts/1&method=PUT"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
       "factory",
       "comment",
       "synchronized",
       "notificationRestricted",
       "notifyPackage"
    ]
    """

  Scenario: A supervisor can edit some field of a subordinate if they don't have ACL_AUTH_INTRANET
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/people/70&method=PUT"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        "erpIdentifier",
        "supervisor",
        "locale",
        "mentor",
        "password"
    ]
    """

  Scenario: A PSM can edit a product even using overriding setters
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/sales/products/13&method=PUT"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        "family",
        "name",
        "hidden",
        "light",
        "description",
        "erpLocation",
        "financeFamily",
        "manufacturingFamily",
        "productStandardItems",
        "innovativeLevel"
    ]
    """

  Scenario: A planner can only edit the standard items of a product
    Given I authenticate as the intranet user "user-planner@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/sales/products/13&method=PUT"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        "productStandardItems"
    ]
    """

  Scenario: Requesting an unknown resource or an unknown HTTP method will return an empty array
    Given I authenticate as the intranet user "user-psm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/fields?iri=/foo&method=POUET"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
    ]
    """
