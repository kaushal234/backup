Feature: Test account receivables

  Scenario: Request all account receivables without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables"
    Then the response status code should be 401

  Scenario: Request a single account receivable without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/1"
    Then the response status code should be 401

  Scenario: account receivables should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/1"
    Then the response status code should be 403

  Scenario: Request all account receivables should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: Request all account receivables should be possible for user accountant (with 2 results)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivables.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Request all account receivables should be possible for user excom (with all results)
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivables.json"
    And the JSON node "hydra:totalItems" should be equal to 3

  Scenario: Request account receivable overviews should not be possible without currency nor sso
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivable_overviews"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Currency and SSO are mandatory filters for this route."

  Scenario: Request account receivable overviews should not be possible when no exchange rate found
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivable_overviews?currency=/finance/currencies/2&customerErpReference.sso[]=/locations/31"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "No exchange rate found for this currency."

  Scenario: Request account receivable overviews should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivable_overviews?currency=/finance/currencies/4&customerErpReference.sso[]=/locations/31"
    Then the response status code should be 403

  Scenario: Request account receivable overviews should be possible for user excom
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivable_overviews?currency=/finance/currencies/1&customerErpReference.sso[]=/locations/31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivable_overviews.json"

  Scenario: Download excel account receivable should be possible for user excom
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/finance/account_receivables?columns=customerErpReference.sso.erp,customerErpReference.customerNumber,customerErpReference.customer,customerErpReference.customer.mainSalesRepresentative.asm,transactionTypeReference.transactionType,erpInvoiceNumber,country,purchaseOrderNumber,invoiceDate,dueDate,currency,originalAmount,originalAmountLocalCurrency,balanceAmount,balanceAmountLocalCurrency,salesOrderNumber,salesOrderDate,financeReferenceA,financeReferenceB,salesReferenceA,salesReferenceB,creditAnalyst,invoiceRecord.expectedPaymentDate,invoiceRecord.revisedDueDate,invoiceRecord.lastComment"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Erp | Pcust | Customer | Asm | Transaction Type | Erp Invoice Number | Country | Purchase Order Number | Invoice Date | Due Date | Currency | Original Amount | Original Amount Local Currency | Balance Amount | Balance Amount Local Currency | Sales Order Number | Sales Order Date | Finance Reference A | Finance Reference B | Sales Reference A | Sales Reference B | Credit Analyst | Expected Payment Date | Revised Due Date | Last Comment |

  Scenario: Download excel account receivable overviews should be possible for user excom
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/finance/account_receivable_overviews?currency=/finance/currencies/1&customerErpReference.sso[]=/locations/31"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Customer  | Erp | Cur | Total Value | Total Past Due | Not Past Due | Past Due 0-30 days | Past Due 31-60 days | Past Due 61-90 days | Past Due 91-180 days | Past Due > 180 days | Past Due % | Past Due > 60 days % |

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\AccountReceivable" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "order[balanceAmount]" should be available and its type should be "string"
    And the filter "order[dueDate]" should be available and its type should be "string"
    And the filter "customerErpReference.customer" should be available and its type should be "string"
    And the filter "customerErpReference.customer.mainSalesRepresentative.asm" should be available and its type should be "string"
    And the filter "customerErpReference.customer.mainSalesRepresentative.asm.supervisor" should be available and its type should be "string"
    And the filter "customerErpReference.customer.customerTypes" should be available and its type should be "string"
    And the filter "customerErpReference.sso" should be available and its type should be "string"
    And the filter "delinquent" should be available and its type should be "bool"
    And the filter "balanceAmount[gt]" should be available and its type should be "string"
    And the filter "country" should be available and its type should be "string"
    And the filter "currency" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Filter account receivables should be possible for user excom with converted value
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables?context[convertTo]=/finance/currencies/3"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivables_amount_converted.json"

  Scenario: Filter account receivables with normalization groups should show invoice record
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables?normalizationGroups[]=invoice_record"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivables_with_invoice.json"

  Scenario: Request a single account receivable should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/2"
    Then the response status code should be 403

  Scenario: Request a single account receivable should be possible for user accountant (only if he has ROLE_AR on SSO of customerErpReference)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivable.json"
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/1"
    Then the response status code should be 403

  Scenario: Directly post an account receivable should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/account_receivables"
    Then the response status code should be 405

  Scenario: Import account receivables from file should be possible for user cfo
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/account_receivables/import_file/31" with file "file" "file.doc"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "File must be Xlsx type."
    Given I authenticate as the intranet user "user-cfo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/account_receivables/import_file/31" with file "file" "file.xlsx"
    Then the response status code should be 204
    And a message of class "App\Message\Finance\AccountReceivableImportFromFile" should have been sent in the bus

  Scenario: Update an account receivable should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/account_receivables/2"
    Then the response status code should be 405
