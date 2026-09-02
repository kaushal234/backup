Feature: When a Sales Orders is shipped in ION, the API receives a request

  Scenario: Shipped Sales Orders can't be posted by intranet users
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/shipped_sales_orders" with body:
    """
    {
      "salesOrderNumber": "SO0000123"
    }
    """
    Then the response status code should be 403

  Scenario: Shipped Sales Orders can't be posted by XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/shipped_sales_orders" with body:
    """
    {
      "salesOrderNumber": "SO0000123"
    }
    """
    Then the response status code should be 403

  Scenario: Shipped Sales Orders can't be posted by an Authorized App without the correct feature
    Given I authenticate as the authorized application "pio"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/shipped_sales_orders" with body:
    """
    {
      "salesOrderNumber": "SO0000123"
    }
    """
    Then the response status code should be 403

  Scenario: Shipped Sales Orders can be posted by an Authorized App with the correct feature
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/shipped_sales_orders" with body:
    """
    {
      "salesOrderNumber": "SO0000123"
    }
    """
    Then the response status code should be 201
    And the JSON node "salesOrderNumber" should be equal to the string "SO0000123"
    And the JSON node "message" should be equal to the string "No SPR was updated"

  Scenario: Shipped Sales Orders can be posted by an Authorized App with the correct feature
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/shipped_sales_orders" with body:
    """
    {
      "salesOrderNumber": "9696969"
    }
    """
    Then the response status code should be 201
    And the JSON node "salesOrderNumber" should be equal to "9696969"
    And the JSON node "message" should be equal to the string "The following SPR were updated: 1, 5"
    And an email should have been sent asynchronously with subject "SPR#1 - shipped"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"
    And an email should have been sent asynchronously with subject "SPR#1 has been shipped"
    And this asynchronous email should be sent only to "julien.lepers@tld.com"
    And an email should have been sent asynchronously with subject "SPR#5 - shipped"
    And this asynchronous email should be sent only to "partCustomerSupport@pcs.fr"
    And an email should have been sent asynchronously with subject "SPR#5 has been shipped"
    And this asynchronous email should be sent only to "julien.lepers@tld.com"
    And a comment should have been inserted on resource "/parts/toc_spare_parts_requests/1" with message "Sales Order #9696969 was closed in LN, the SPR status has been switched to SHIPPED" without a username
    And a comment should have been inserted on resource "/parts/sb_spare_parts_requests/5" with message "Sales Order #9696969 was closed in LN, the SPR status has been switched to SHIPPED" without a username


