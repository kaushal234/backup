Feature: When the estimated gt date of Equipment record is updated in ION, the API receives a request

  Scenario: Estimated green tag date equipment record can't be posted by intranet users
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "T70021",
      "estimatedGreenTagDate": "2021-02-01"
    }
    """
    Then the response status code should be 403

  Scenario: Estimated green tag date equipment record can't be posted by XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "T70021",
      "estimatedGreenTagDate": "2021-02-01"
    }
    """
    Then the response status code should be 403

  Scenario: Estimated green tag date equipment record can't be posted by an Authorized App without the correct feature
    Given I authenticate as the authorized application "PIO"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "T70021",
      "estimatedGreenTagDate": "2021-02-01"
    }
    """
    Then the response status code should be 403

  Scenario: Estimated green tag date equipment record can't be posted by an Authorized App with the correct feature if green tag date is already set
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "equipment_records/18"
    Then the response status code should be 200
    And the JSON node "estimatedGreenTagDate" should be null
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "T70021",
      "estimatedGreenTagDate": "2021-02-01"
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "equipment_records/18"
    Then the response status code should be 200
    And the JSON node "estimatedGreenTagDate" should be null

  Scenario: Estimated green tag date equipment record can be posted by an Authorized App with the correct feature
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "40850",
      "estimatedGreenTagDate": "2021-02-01"
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "equipment_records/17"
    Then the response status code should be 200
    And the JSON node "estimatedGreenTagDate" should be equal to the string "2021-02-01T00:00:00-05:00"

  Scenario: Estimated green tag date important gap send an email to CMO, when the update comes from Infor LN
    Given I authenticate as the authorized application "ION"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/estimated_green_tag_date_equipment_records" with body:
    """
    {
      "equipmentRecord": "T800",
      "estimatedGreenTagDate": "2050-02-01"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Escalation estimated GT date with important deviation"
    And this asynchronous email should be sent to "user-gcoo@tld.fr"
    And this asynchronous email should be sent to "user-cmo@tld.fr"
