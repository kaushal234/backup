Feature: Test sales customers double write API

  Scenario: Create a sales customer in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customers" with the body "tests/fixtures/json/sales/customer/dummies/post.json"
    Then the response status code should be 201
    And a new row has been inserted in the legacy table "customers"
    And a new row has been inserted in the legacy table "mod_logs"

  Scenario: Update a sales customer in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1" with the body "tests/fixtures/json/sales/customer/dummies/put.json"
    Then the response status code should be 200
    # /sales/customers/4 legacy Id must be 4077
    And the column "parent_id" from the "customers" legacy table has been updated with integer 4077
    And the column "customer_name" from the "customers" legacy table has been updated with string "AIR PES - &#31354;&#27668;"
    And the column customer_address from the customers legacy table has been updated with a string containing "calle\nJon\nCP 17700 Madrid"
    And the column "customer_tel" from the "customers" legacy table has been updated with string "+33 5 46 07 72 19"
    And the column "customer_fax" from the "customers" legacy table has been updated with string "+33 2 34 56 78 91"
    # /country/4 legacyId = 251
    And the column "ctry_id" from the "customers" legacy table has been updated with integer 251
    And the column "hidden" from the "customers" legacy table has been updated with integer 1
    And the column "url" from the "customers" legacy table has been updated with string "http://airp.es"
    And the column "type" from the "customers" legacy table has been updated with string "Top, Pot"
    # /people/12 legacyId = 2359
    And the column "asm_id" from the "customers" legacy table has been updated with integer 2359
    And the column "logo_file" from the "customers" legacy table has not been updated
    And the column "customer_em_jira_key" from the "customers" legacy table has been updated with string "TEST"
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Change a sales customer approval status in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1/status" with body:
    """
    {
      "status": "NOT APPROVED"
    }
    """
    Then the response status code should be 200
    And the column "approved" from the "customers" legacy table has been updated with integer 0
    And a new row has been inserted in the legacy table "mod_logs"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1/status" with body:
    """
    {
      "status": "APPROVED"
    }
    """
    Then the response status code should be 200
    And the column "approved" from the "customers" legacy table has been updated with integer 1
    And a new row has been inserted in the legacy table "mod_logs"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/customers/1/status" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 200
    And the column "approved" from the "customers" legacy table has been updated with integer 0
    And 2 new rows have been inserted in the legacy table "mod_logs"

  Scenario: Update a sales customer logo in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/customers/1/logo" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the column "logo_file" from the "customers" legacy table has been updated with filename "customers/00001-air-pes-kong-qi.jpg"

  Scenario: Delete a customer double write in legacy database
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    When I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "DELETE" request to "/sales/customers/40"
    Then the response status code should be 204
    And the column "deleted_at" from the "customers" legacy table has been updated
    And 0 rows have been deleted in the legacy table "customers"
