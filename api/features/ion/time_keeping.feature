Feature: Time keeping can be posted to the ERP through the API

  Scenario: A user needs to be authenticated to post a time keeping record
    Given I add "Accept" header equal to "application/ld+json"
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/time_keepings"
    Then the response status code should be 401

  Scenario: An authenticated user can post an indirect timekeeping transaction
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/time_keepings" with body:
    """
    {
      "employeeNumber": "PADM",
      "transactionType": "INDIRECT",
      "task": "173"
    }
    """
    Then the response status code should be 201
    And a "HoursAccounting_TLD" SOAP client has been created
    And this client has been called on the operation "PostHours" with the following request:
    """
    {
      "DataArea": {
        "HoursAccounting_TLD": {
          "Employee": "PADM",
          "Transaction": "INDIRECT",
          "ProductionOrder": null,
          "Operation": "",
          "Task": "173",
          "Text": ". Logging info: date: 2022-11-23, time: 23:10:10 +8",
          "TransactionDate": "2022-11-23",
          "TransactionTime": "23:10:10 +8"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "@type" should be equal to the string "TimeKeepingPostTransactionOutput"
    And the JSON node "lines[0].employeeNumber" should be equal to the string "PADM"
    And the JSON node "lines[0].transactionType" should be equal to the string "INDIRECT"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/time_keeping/schemas/time_keeping.json"

  Scenario: An authenticated user can post a direct timekeeping transaction
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/time_keepings" with body:
    """
    {
      "employeeNumber": "21499",
      "transactionType": "DIRECT",
      "operationNumber": 10,
      "orderNumber": "W22801951"
    }
    """
    Then the response status code should be 201
    And a "HoursAccounting_TLD" SOAP client has been created
    And this client has been called on the operation "PostHours" with the following request:
    """
    {
      "DataArea": {
        "HoursAccounting_TLD": {
          "Employee": "21499",
          "Transaction": "DIRECT",
          "ProductionOrder": "W22801951",
          "Operation": "10",
          "Task": null,
          "Text": ". Logging info: date: 2022-11-23, time: 23:10:10 +8",
          "TransactionDate": "2022-11-23",
          "TransactionTime": "23:10:10 +8"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "@type" should be equal to the string "TimeKeepingPostTransactionOutput"
    And the JSON node "lines[0].employeeNumber" should be equal to "21499"
    And the JSON node "lines[0].transactionType" should be equal to the string "DIRECT"
    And the JSON node "lines[0].status" should be equal to the string "CLOCKIN-IN"
    And the JSON node "lines[0].productionOrder" should be equal to the string "W22801951"
    And the JSON node "lines[0].operationNumber" should be equal to 10
    And the JSON node "lines[0].transactionType" should be equal to the string "DIRECT"
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/time_keeping/schemas/time_keeping.json"

#    temporary deactivated, see SP-759
#  Scenario: An authenticated user can post a multi-direct timekeeping transaction
#    Given I authenticate as the intranet user "user-basic@tld.fr"
#    And I add "Accept" header equal to "application/ld+json"
#    And I add "Content-type" header equal to "application/ld+json"
#    When I send a "POST" request to "/ion/time_keepings" with body:
#    """
#    {
#      "employeeNumber": "21499",
#      "transactionType": "DIRECT-MULTI",
#      "orderNumber": "W22801995",
#      "operationNumber": 10
#    }
#    """
#    Then the response status code should be 201
#    And a "HoursAccounting_TLD" SOAP client has been created
#    And this client has been called on the operation "PostHours" with the following request:
#    """
#    {
#      "DataArea": {
#        "HoursAccounting_TLD": {
#          "Employee": "21499",
#          "Transaction": "DIRECT-MULTI",
#          "ProductionOrder": "W22801995",
#          "Operation": "10",
#          "Task": null,
#          "Text": ". Logging info: date: 2022-11-23, time: 23:10:10 +8",
#          "TransactionDate": "2022-11-23",
#          "TransactionTime": "23:10:10 +8"
#        }
#      }
#    }
#    """
#    And a total of 1 request has been sent to ION
#    And the JSON node "@type" should be equal to the string "TimeKeepingPostTransaction"
#    And the JSON node "lines[0].employeeNumber" should be equal to "21499"
#    And the JSON node "lines[0].transactionType" should be equal to the string "DIRECT"
#    And the JSON node "lines[0].status" should be equal to the string "CLOCKIN-OUT"
#    And the JSON node "lines[0].productionOrder" should be equal to the string "W22801951"
#    And the JSON node "lines[0].operationNumber" should be equal to 10
#    And the JSON node "lines[1].employeeNumber" should be equal to "21499"
#    And the JSON node "lines[1].transactionType" should be equal to the string "DIRECT-MULTI"
#    And the JSON node "lines[1].status" should be equal to the string "CLOCKIN-IN"
#    And the JSON node "lines[1].productionOrder" should be equal to the string "W22801995"
#    And the JSON node "lines[1].operationNumber" should be equal to 10
#    And the JSON should be valid according to the schema "tests/fixtures/json/ion/time_keeping/schemas/time_keeping.json"

  Scenario: An authenticated user can post a end-active timekeeping transaction, to end all its transactions
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/ion/time_keepings" with body:
    """
    {
      "employeeNumber": "21499",
      "transactionType": "END-ACTIVE"
    }
    """
    Then the response status code should be 201
    And a "HoursAccounting_TLD" SOAP client has been created
    And this client has been called on the operation "PostHours" with the following request:
    """
    {
      "DataArea": {
        "HoursAccounting_TLD": {
          "Employee": "21499",
          "Transaction": "END-ACTIVE",
          "ProductionOrder": null,
          "Operation": "",
          "Task": null,
          "Text": ". Logging info: date: 2022-11-23, time: 23:10:10 +8",
          "TransactionDate": "2022-11-23",
          "TransactionTime": "23:10:10 +8"
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    And the JSON node "@type" should be equal to the string "TimeKeepingPostTransactionOutput"
    And the JSON node "lines[0].employeeNumber" should be equal to "21499"
    And the JSON node "lines[0].transactionType" should be equal to the string "DIRECT"
    And the JSON node "lines[0].status" should be equal to the string "CLOCKIN-OUT"
    And the JSON node "lines[0].productionOrder" should be equal to the string "W22801951"
    And the JSON node "lines[0].operationNumber" should be equal to 10
    And the JSON should be valid according to the schema "tests/fixtures/json/ion/time_keeping/schemas/time_keeping.json"
