Feature: Test people transfer

  Scenario: Test that I can't transfer a people as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/10/transfer" with body:
    """
    {
        "target": "/people/12"
    }
    """
    Then the response status code should be 403

  Scenario: Test that I can transfer a people even if he had no closest airport and no premise
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/10/transfer" with body:
    """
    {
        "target": "/people/12"
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    And the JSON node "hidden" should be false
    And the JSON node "alternateEmail" should be null

  Scenario: Test a supervisor has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/29"
    Then the response status code should be 200
    Then the JSON node "supervisor.@id" should be equal to the string "/people/12"

  Scenario: Test a supervisor has been transferred to one of his own team members
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/48/transfer" with body:
    """
    {
        "target": "/people/74"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/74"
    Then the response status code should be 200
    Then the JSON node "supervisor.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/people/79"
    Then the response status code should be 200
    Then the JSON node "supervisor.@id" should be equal to the string "/people/74"

  Scenario: Test a news has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/news/28"
    Then the response status code should be 200
    Then the JSON node "people.@id" should be equal to the string "/people/12"

  Scenario: Test a module has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/modules/1"
    Then the response status code should be 200
    Then the JSON node "operationalOwner.@id" should be equal to the string "/people/12"

  Scenario: Test a business unit representative has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/business_units/17"
    Then the response status code should be 200
    Then the JSON node "representative.@id" should be equal to the string "/people/12"

  Scenario: Test a location representative has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/locations/15"
    Then the response status code should be 200
    Then the JSON node "representative.@id" should be equal to the string "/people/12"

  Scenario: Test a location area supervisor has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/location_areas/17"
    Then the response status code should be 200
    Then the JSON node "supervisor.@id" should be equal to the string "/people/12"

  Scenario: Test an FAQ buyer has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/26"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "REJECTED"
    Then the JSON node "buyer.@id" should be equal to the string "/people/10"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/23"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "IN_PROGRESS"
    Then the JSON node "buyer.@id" should be equal to the string "/people/12"

  Scenario: Test an FAQ poster has been transferred if the status allows it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/24"
    Then the JSON node "status" should be equal to the string "QUALIFIED"
    Then the JSON node "poster.@id" should be equal to the string "/people/10"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/27"
    Then the JSON node "status" should be equal to the string "PENDING"
    Then the JSON node "poster.@id" should be equal to the string "/people/12"

  Scenario: Test an FAQ owner has been transferred if the status allows it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/25"
    Then the JSON node "status" should be equal to the string "REJECTED"
    Then the response status code should be 200
    Then the JSON node "owner.@id" should be equal to the string "/people/10"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/28"
    Then the JSON node "status" should be equal to the string "PENDING"
    Then the response status code should be 200
    Then the JSON node "owner.@id" should be equal to the string "/people/12"

  Scenario: Test a sales order's asm are transferred when the status allows it
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/3"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "PENDING"
    Then the JSON node "asm.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/orders/4"
    Then the JSON node "status" should be equal to the string "CLOSED"
    Then the response status code should be 200
    Then the JSON node "asm.@id" should be equal to the string "/people/10"

  Scenario: Test a Meeting has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/3"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "OPEN"
    Then the JSON node "createdBy.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/4"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "RELEASED"
    Then the JSON node "createdBy.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/5"
    Then the response status code should be 200
    Then the JSON node "status" should be equal to the string "CLOSED"
    Then the JSON node "createdBy.@id" should be equal to the string "/people/10"

  Scenario: Test the assignee of a current action of meeting is transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/actions/2"
    Then the response status code should be 200
    Then the JSON node "completed" should be false
    Then the JSON node "assignee" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/actions/3"
    Then the response status code should be 200
    Then the JSON node "completed" should be true
    Then the JSON node "assignee" should be equal to the string "/people/10"

  Scenario: Test the assignee of a VWC is transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/purchasing/ncr_vendor_warranty_claims/5"
    Then the response status code should be 200
    Then the JSON node "assignee.@id" should be equal to the string "/people/12"

  Scenario: Test the assignee and MIS assignee of a TTS is transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/6"
    Then the response status code should be 200
    Then the JSON node "assignee.@id" should be equal to the string "/people/12"
    Then the JSON node "misAssignee.@id" should be equal to the string "/people/12"
    Then the JSON node "createdBy.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/mis/trouble_tickets/7"
    Then the response status code should be 200
    Then the JSON node "assignee.@id" should be equal to the string "/people/10"
    Then the JSON node "misAssignee.@id" should be equal to the string "/people/10"

  Scenario: Test the assignee and Creator of task is transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/people/13/transfer" with body:
    """
    {
        "target": "/people/12"
    }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/9"
    Then the response status code should be 200
    Then the JSON node "createdBy.@id" should be equal to the string "/people/12"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/tasks/10"
    Then the response status code should be 200
    Then the JSON node "assignee.@id" should be equal to the string "/people/13"
    Then the JSON node "createdBy.@id" should be equal to the string "/people/13"

  Scenario: Test a follower has been deleted
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/subscriptions/2"
    Then the response status code should be 404

  Scenario: Test that I can transfer an ASM
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # people 47 is @user_asm_transferred
    When I send a "PUT" request to "/people/47/transfer" with body:
    """
    {
        "target": "/people/12",
        "asmTarget": "/people/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true

  Scenario: Test that I can transfer a sales representative
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # people 41 is @user_sales
    When I send a "PUT" request to "/people/41/transfer" with body:
    """
    {
        "target": "/people/12",
        "salesRepTarget": "/people/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    # Reset the user so it can be use elsewhere
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/people/41" with body:
    """
    {
      "hidden": false,
      "disabled": false
    }
    """

  Scenario: Test that I can transfer a parts representative
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # people 39 is @user_parts
    When I send a "PUT" request to "/people/39/transfer" with body:
    """
    {
        "target": "/people/12",
        "partsRepTarget": "/people/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true
    # Reset the user so it can be use elsewhere
    Then I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    And I send a "PUT" request to "/people/39" with body:
    """
    {
      "hidden": false,
      "disabled": false
    }
    """
    Then the response status code should be 200

  Scenario: Test that I can transfer a service representative
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # people 1 is @user_basic
    When I send a "PUT" request to "/people/1/transfer" with body:
    """
    {
        "target": "/people/12",
        "serviceRepTarget": "/people/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "disabled" should be true

  Scenario: Test that I can transfer a service representative without disabling him
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    # people 40 is @user_service
    When I send a "PUT" request to "/people/40/transfer" with body:
    """
    {
        "serviceRepTarget": "/people/11"
    }
    """
    Then the response status code should be 200
    And the JSON node "hidden" should be false
    And the JSON node "disabled" should be false

  Scenario: Test a customer has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customers/32"
    Then the response status code should be 200
    Then the JSON node "mainSalesRepresentative.asm.@id" should be equal to the string "/people/11"

  Scenario: Test a sfr has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/sales_forecasts/4"
    Then the response status code should be 200
    Then the JSON node "asm.@id" should be equal to the string "/people/11"

  Scenario: Test a sales representative has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams/2"
    Then the response status code should be 200
    Then the JSON node "salesRepresentative.@id" should be equal to the string "/people/11"

  Scenario: Test a parts representative has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams/3"
    Then the response status code should be 200
    Then the JSON node "partsRepresentative.@id" should be equal to the string "/people/11"

  Scenario: Test a service representative has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/customer_relationship_teams/4"
    Then the response status code should be 200
    Then the JSON node "serviceRepresentative.@id" should be equal to the string "/people/11"

  Scenario: Test a demo asm has been transferred
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/demos/3"
    Then the response status code should be 200
    Then the JSON node "asm.@id" should be equal to the string "/people/11"
