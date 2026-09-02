Feature: Employee can be fetched through the API

  Scenario: Request a single Employee without being authenticated should not be permitted
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees?itemsPerPage=10"
    Then the response status code should be 401
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees/640124"
    Then the response status code should be 401

  Scenario: Employees should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees?itemsPerPage=10"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees/1001"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\ION\Resources\MasterData\EnterpriseModel\Employee" is exposed on the API
    Then the filter "logicalOperator" should be available and its type should be "string"
    Then the ION filter "fullName" should be available and its type should be "string"

  Scenario: Request a collection of Employees
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees?logicalOperator=or&fullName[eq][]=PERSYN DELPHINE&itemsPerPage=1"
    And a "Employee_v2" SOAP client has been created
    And this client has been called on the operation "List" with the following request:
    """
    {
      "ControlArea": {
        "maxNumberOfObjects": 1,
        "Filter": {
          "LogicalExpression": {
            "logicalOperator": "or",
            "ComparisonExpression": [
              {
                "comparisonOperator": "eq",
                "instanceValue": "PERSYN DELPHINE",
                "attributeName": "Employee_v2.fullName"
              }
            ]
          }
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "hydra:member[0].@id" should be equal to the string "/ion/employees/1001"
    And the JSON node "hydra:member[0].fullName" should be equal to the string "PERSYN DELPHINE"
    And the JSON node "hydra:member[0].employeeCode" should be equal to "1001"
    And the JSON node "hydra:member[0].erp" should be equal to "560"
    And the JSON node "hydra:member[0].baanLegacyId" should be equal to "1"
    And the JSON node "hydra:member[0].emailAddress" should be equal to the string "delphine.persyn@tld-europe.com"
    And the JSON node "hydra:member" should have 1 elements
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/employees/schemas/collection.json"

  Scenario: Request a single Employee
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/ion/employees/1001"
    And a "Employee_v2" SOAP client has been created
    And this client has been called on the operation "Show" with the following request:
    """
    {
      "DataArea": {
        "Employee_v2": {
          "employeeCode": "1001"
        }
      }
    }
    """
    Then the response status code should be 200
    And the JSON node "employeeCode" should be equal to "1001"
    And the JSON node "fullName" should be equal to "PERSYN DELPHINE"
    And the JSON node "erp" should be equal to "560"
    And the JSON node "baanLegacyId" should be equal to "1"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/employees/schemas/item.json"
