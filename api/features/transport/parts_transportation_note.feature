Feature: Test note can be created and updated

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Parts\TransportationNote" should only be available for intranet user

  Scenario: Note should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transportation_note/schemas/transportation_notes.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Parts\TransportationNote" is exposed on the API
    Then the filter "order[updatedAt]" should be available and its type should be "string"

  Scenario: Note detail should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transportation_note/schemas/transportation_note.json"

  Scenario: Create a note without permission should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/transportation_notes" with the body "tests/fixtures/json/transportation_note/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a note
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/transportation_notes" with the body "tests/fixtures/json/transportation_note/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/transportation_note/schemas/transportation_note.json"
    And the JSON node "country.@id" should be equal to the string "/countries/4"
    And the JSON node "updatedAt" should be newer than 1 minute ago
    And the JSON node "note" should be equal to the string "Le flash c’est de l’Adobe"

  Scenario: Create an invalid note without should trigger business validation rules
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/transportation_notes" with body:
    """
      {}
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "country"
    And the JSON node "violations[0].message" should be equal to "This value should not be null."
    And the JSON node "violations[1].propertyPath" should be equal to "note"
    And the JSON node "violations[1].message" should be equal to "This value should not be null."

  Scenario: Can't create duplicated notes for a country
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/parts/transportation_notes" with the body "tests/fixtures/json/transportation_note/dummies/post.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "country"
    And the JSON node "violations[0].message" should be equal to "This value is already used."

  Scenario: Update a note without permission should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/transportation_notes/1" with the body "tests/fixtures/json/transportation_note/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a note
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/parts/transportation_notes/1" with the body "tests/fixtures/json/transportation_note/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/transportation_note/schemas/transportation_note.json"
    And the JSON node "country.@id" should be equal to the string "/countries/1"
    And the JSON node "updatedAt" should be newer than 1 minute ago
    And the JSON node "note" should be equal to the string "un geek ne crie pas il URL"

  Scenario: Upload a file to a note without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/transportation_notes/1/files" with file "file" "file.pdf"
    Then the response status code should be 403

  @resetFileTable
  Scenario: Upload a file to a note with permission
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/transportation_notes/1/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/parts/transportation_notes/1" with a changeset on the property "files"
    # Check that the item schema is still valid
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/1"
    Then the response status code should be 200
    And the JSON node "files" should have 1 element
    And the JSON should be valid according to the schema "tests/fixtures/json/transportation_note/schemas/transportation_note.json"

  Scenario: Upload an invalid file to a note
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/parts/transportation_notes/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a note file without permission should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/1/files/1"
    Then the response status code should be 403

  Scenario: Download a note file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/1/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/2/files/1"
    Then the response status code should be 404

  Scenario: Delete a note file without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/transportation_notes/1/files/1"
    Then the response status code should be 403

  Scenario: Delete a note file with permission
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/transportation_notes/2/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/parts/transportation_notes/1/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/parts/transportation_notes/1" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-spm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/parts/transportation_notes/1"
    And the JSON node "files" should have 0 element

  @resetFileTable
  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test
