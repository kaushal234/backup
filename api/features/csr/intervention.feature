Feature: Test Intervention Entity

  Scenario: Request all Interventions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/interventions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/interventions.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Service\CustomerServiceRecord\Intervention" is exposed on the API
    Then the filter "customerServiceRecord" should be available and its type should be "string"
    Then the filter "leader" should be available and its type should be "string"

  Scenario: Request a single Intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/interventions/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/intervention.json"

  Scenario: Request a non existing CSR
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/interventions/not_found"
    Then the response status code should be 404

  Scenario: Request a single CSR by an XU is not permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/service/interventions/1"
    Then the response status code should be 403

  Scenario: As a user, I can't create an intervention with bad CSR status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/interventions" with body:
    """
    {
        "leader": "/people/65",
        "operators": [
            "/people/11",
            "/people/12"
        ],
        "plannedAt" : "2022-07-20T05:02:14-04:00",
        "customerServiceRecord": "/service/customer_service_records/1"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "An intervention cannot be created without the correct status on Customer Service Record"

  Scenario: As a user, I can't create an intervention with a bad leader
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/interventions" with body:
    """
    {
        "leader": "/people/13",
        "operators": [
            "/people/11",
            "/people/12"
        ],
        "plannedAt" : "2022-07-20T05:02:14-04:00",
        "customerServiceRecord": "/service/customer_service_records/2"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "leader: An AST, a CSTL, a CSM or a CSS position is mandatory to be leader on the intervention"

  Scenario: As a user, I can create an intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/service/interventions" with body:
    """
    {
        "leader": "/people/65",
        "operators": [
            "/people/11",
            "/people/12"
        ],
        "plannedAt" : "2022-07-20T05:02:14-04:00",
        "customerServiceRecord": "/service/customer_service_records/2"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/csr/schemas/intervention.json"

  Scenario: Update an Open Intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
        "operators": [
            "/people/14",
            "/people/15"
        ]
    }
    """
    Then the response status code should be 200
    And the JSON node "operators" should have 2 element
    And the JSON node "operators[0].@id" should be equal to "/people/14"
    And the JSON node "operators[1].@id" should be equal to "/people/15"

  Scenario: Update an Intervention status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "status": "STARTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "STARTED"

  Scenario: Update an Intervention with bad status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "Status PENDING is not allowed."

  Scenario: I can't update status on an Intervention without ended date
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "status": "SOLVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "status: Ended date is required to close the intervention"

  Scenario: I can update the intervention status to SOLVED and the CSR status changes to Completed and the ER commissioning date is updated
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "endedAt": "2026-07-09 12:00:00",
      "status": "SOLVED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "SOLVED"
    And the JSON node "endedAt" should be equal to "2026-07-09T12:00:00-04:00"
    And the JSON node "customerServiceRecord" should be equal to the string "/service/commissioning_customer_service_records/5"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/service/customer_service_records/5"
    Then the response status code should be 200
    And the JSON node "status" should be equal to "COMPLETED"
    And the JSON node "equipmentRecord.dateCommissioned" should be equal to "2026-07-09T12:00:00-04:00"

    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "operators": [
            "/people/15",
            "/people/16"
        ]
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "You can't update operators on a closed Intervention"

  Scenario: I can't delete operators on a non open Intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "operators": [
        ]
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "You can't update operators on a closed Intervention"

  Scenario: I can't delete and update operators on a non open Intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/service/interventions/2" with body:
    """
    {
      "operators": [
          "/people/16"
        ]
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "You can't update operators on a closed Intervention"

  Scenario: Delete an intervention
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/service/interventions/2"
    Then the response status code should be 204