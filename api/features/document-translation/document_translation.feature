Feature: Document Translation

  Scenario: As extranet user i can't see list of documents
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations"
    Then the response status code should be 403

  Scenario: As vendor users i can't see list of documents
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations"
    Then the response status code should be 403

  Scenario: As user-basic i can see my list of documents
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/document_translation/schemas/document_translations.json"
    And the JSON node "hydra:totalItems" should be equal to 3

  Scenario: As user-superuser i can see my list of documents
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/document_translation/schemas/document_translations.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: I can't refresh a document that is not mine
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations/4/refresh"
    Then the response status code should be 404
    And the JSON node "hydra:description" should be equal to "Not Found"

  Scenario: I can't download a document not in ready status
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/document_translations/1/download"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "Document is not in status ready (queued)."

  Scenario: I can't download a document already expired or already downloaded
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "GET" request to "/document_translations/2/download"
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to "The translated file is no longer available (expired or already downloaded) (Deepl HTTP 404)."

  Scenario: As user-basic i can't request a translation for a .doc file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/document_translation" with file "file" "file.doc"
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to "mimeType: The value you selected is not a valid choice."