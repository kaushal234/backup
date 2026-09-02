Feature: Test summarize endpoints of AI API
  Scenario: Summarize through AI without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/1"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/sales_forecasts/1"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/technician_on_calls/1"
    Then the response status code should be 401

  Scenario: Summarize through AI should be accessible only for intranet users
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/3234"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/3234"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/sales_forecasts/1"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/sales_forecasts/1"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/technician_on_calls/1"
    Then the response status code should be 403
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/technician_on_calls/1"
    Then the response status code should be 403

  Scenario: Summarize a dms should be possible for non confidential
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/3234"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/summary/schemas/summary.json"

  Scenario: Summarize a TOC
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/technician_on_calls/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/summary/schemas/summary.json"

  Scenario: Summarize a sales forecast (SFR) should be allowed as superuser
    #adding a comment so it is summarizable
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/sales/sales_forecasts/7",
      "message": "Hello world"
    }
    """
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/sales_forecasts/7"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/summary/schemas/summary.json"

  Scenario: Request a Summary of a sales forecast (SFR) i'm not allowed to see, should be forbidden
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/sales_forecasts/7"
    Then the response status code should be 403

  Scenario: Confidential DMS should be accessible by only some users
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/2534"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-csm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/2534"
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-powerbi@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/2534"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/summary/schemas/summary.json"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ai/summarize/dms/2534"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/ai/summary/schemas/summary.json"


