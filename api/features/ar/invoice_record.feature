Feature: Test invoice records

  Scenario: Request all invoice records without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records"
    Then the response status code should be 401

  Scenario: Request a single invoice record without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records/1"
    Then the response status code should be 401

  Scenario: invoice records should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records/1"
    Then the response status code should be 403

  Scenario: Request all invoice records should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records"
    Then the response status code should be 200
    And the JSON node "hydra:totalItems" should be equal to 0

  Scenario: Request all invoice records should be possible for user accountant (with 2 results)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_records.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Request all invoice records should be possible for user excom (with all results)
    Given I authenticate as the intranet user "user-excom@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_records.json"
    And the JSON node "hydra:totalItems" should be equal to 3

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Finance\InvoiceRecord" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    And the filter "customerErpReference.customer" should be available and its type should be "string"
    And the filter "customerErpReference.sso" should be available and its type should be "string"
    And the filter "customerErpReference" should be available and its type should be "string"

  Scenario: Request a single invoice record should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records/1"
    Then the response status code should be 403

  Scenario: Request a single invoice record should be possible for user accountant (only if he has ROLE_AR on SSO of customerErpReference)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_record.json"
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/invoice_records/1"
    Then the response status code should be 403

  Scenario: Create invoice record should not be possible for user basic
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/1",
      "currency": "/finance/currencies/3",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0123",
      "expectedPaymentDate": "2099-04-19"
    }
    """
    Then the response status code should be 403

  Scenario: Create invoice record should be possible for user accountant (only if he has ROLE_AR on SSO of customerErpReference)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/2",
      "currency": "/finance/currencies/3",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0123"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "Either expected payment date AND category OR revised due date only should be set."

  Scenario: Create invoice record should be possible for user accountant (only if he has ROLE_AR on SSO of customerErpReference)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/1",
      "currency": "/finance/currencies/1",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0123",
      "expectedPaymentDate": "2099-04-19"
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/2",
      "currency": "/finance/currencies/1",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0123",
      "expectedPaymentDate": "2099-04-19",
      "revisedDueDate": "2098-04-19",
      "comment": "Apisme"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_record.json"
    And the JSON node "customerErpReference.@id" should be equal to the string "/sales/customer_erp_references/2"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/1"
    And the JSON node "originalAmount" should be equal to the number 69.69
    And the JSON node "category" should be equal to the string "DISPUTE"
    And the JSON node "invoiceNumber" should be equal to the string "0123"
    And the JSON node "expectedPaymentDate" should be equal to the string "2099-04-19T00:00:00-04:00"
    And the JSON node "revisedDueDate" should be equal to the string "2098-04-19T00:00:00-04:00"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/comments?resource=/finance/invoice_records/4&order[createdAt]=DESC"
    Then the JSON node "hydra:member[0].message" should be equal to the string "Apisme"

  Scenario: Create invoice record should be possible for user evp (not if a record already exist for this invoice)
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/2",
      "currency": "/finance/currencies/1",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0123",
      "expectedPaymentDate": "2099-04-19",
      "revisedDueDate": "2098-04-19"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].message" should be equal to the string "A record already exist for this invoice."
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/2",
      "currency": "/finance/currencies/3",
      "originalAmount": 69.69,
      "category": "DISPUTE",
      "invoiceNumber": "0456",
      "expectedPaymentDate": "2099-04-19",
      "revisedDueDate": "2098-04-19"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_record.json"
    And the JSON node "customerErpReference.@id" should be equal to the string "/sales/customer_erp_references/2"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/3"
    And the JSON node "originalAmount" should be equal to the number 69.69
    And the JSON node "category" should be equal to the string "DISPUTE"
    And the JSON node "invoiceNumber" should be equal to the string "0456"
    And the JSON node "expectedPaymentDate" should be equal to the string "2099-04-19T00:00:00-04:00"
    And the JSON node "revisedDueDate" should be null

  Scenario: Update invoice record should be possible for user accountant (only if he has ROLE_AR on SSO of customerErpReference, and only some fields)
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/invoice_records/1" with body:
    """
    {
      "originalAmount": 999.99
    }
    """
    Then the response status code should be 403
    Given I authenticate as the intranet user "user-accountant@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/invoice_records/2" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/1",
      "currency": "/finance/currencies/4",
      "originalAmount": 599.99,
      "category": "OTHER",
      "invoiceNumber": "00000",
      "expectedPaymentDate": "2040-04-19",
      "revisedDueDate": "2039-04-19"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_record.json"
    And the JSON node "customerErpReference.@id" should not be equal to the string "/sales/customer_erp_references/1"
    And the JSON node "currency.@id" should not be equal to the string "/finance/currencies/4"
    And the JSON node "originalAmount" should not be equal to the number 599.99
    And the JSON node "category" should be equal to the string "OTHER"
    And the JSON node "invoiceNumber" should not be equal to the string "00000"
    And the JSON node "expectedPaymentDate" should be equal to the string "2040-04-19T00:00:00-04:00"
    And the JSON node "revisedDueDate" should be equal to the string "2039-04-19T00:00:00-04:00"

  Scenario: Set revised due date of invoice record should be possible for user SAM (only if he has ROLE_SAM on SSO of customerErpReference)
    Given I authenticate as the intranet user "user-sam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/finance/invoice_records" with body:
    """
    {
      "customerErpReference": "/sales/customer_erp_references/2",
      "currency": "/finance/currencies/4",
      "originalAmount": 599.99,
      "category": "OTHER",
      "invoiceNumber": "14567",
      "expectedPaymentDate": "2040-04-19",
      "revisedDueDate": "2039-04-19"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/invoice_record/schemas/invoice_record.json"
    And the JSON node "customerErpReference.@id" should be equal to the string "/sales/customer_erp_references/2"
    And the JSON node "currency.@id" should be equal to the string "/finance/currencies/4"
    And the JSON node "originalAmount" should be equal to the number 599.99
    And the JSON node "category" should be equal to the string "OTHER"
    And the JSON node "invoiceNumber" should be equal to "14567"
    And the JSON node "expectedPaymentDate" should be equal to the string "2040-04-19T00:00:00-04:00"
    And the JSON node "revisedDueDate" should be equal to the string "2039-04-19T00:00:00-04:00"

  Scenario: Set the revised due date more recent than 60 days ago should set AR not delinquent for constraints
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/finance/invoice_records/3" with body and replace "revisedDueDate" by the date "today":
    """
      {
        "revisedDueDate": "1984-01-07"
      }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/finance/account_receivables/3"
    And the JSON should be valid according to the schema "tests/fixtures/json/account_receivable/schemas/account_receivable.json"
    And the JSON node "delinquent" should be false
