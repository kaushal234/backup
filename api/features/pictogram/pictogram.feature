Feature: Test Pictogram API

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Engineering\Pictogram\Pictogram" is exposed on the API
    Then the filter "description" should be available and its type should be "string"
    Then the filter "category" should be available and its type should be "string"
    And the filter "id" should be available and its type should be "int"
    And the filter "order[category.name]" should be available and its type should be "string"
    And the filter "order[description]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"

  Scenario: Request all pictograms
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictograms"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictograms.json"

  Scenario: Request pictogram with filters
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictograms?description=parking"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictograms.json"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0].description" should be equal to the string "Indicates parking brake removal request"
    And the JSON node "hydra:member[0].category.name" should be equal to the string "Engine"
    And the JSON node "hydra:member[0].picture" should be null

  Scenario: Request one pictogram
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictograms/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictogram.json"
    And the JSON node "@id" should be equal to the string "/engineering/pictograms/2"
    And the JSON node "id" should be equal to the string "2"
    And the JSON node "description" should be equal to the string "Indicates parking brake removal request"
    And the JSON node "category.name" should be equal to the string "Engine"
    And the JSON node "picture" should be null

  Scenario: As a engineer, I can create a pictogram
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/engineering/pictograms" with body:
    """
    {
      "description": "Test new pictogram",
      "category": "/engineering/pictogram/categories/2"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictogram.json"
    And the JSON node "description" should be equal to the string "Test new pictogram"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/engineering/pictograms/6/picture" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a engineer, I can add more files to pictogram
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/engineering/pictograms/6/files" with file "file" "image_1200x1200.png"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a engineer, I can get files and picture
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictograms/6"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictogram.json"
    And the JSON node "@id" should be equal to the string "/engineering/pictograms/6"
    And the JSON node "id" should be equal to the string "6"
    And the JSON node "description" should be equal to the string "Test new pictogram"
    And the JSON node "category.name" should be equal to the string "Braking"
    And the JSON node "picture.filePath" should not be null
    And the JSON node "files" should have 1 element

  Scenario: As a engineer, I can update a pictogram
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/engineering/pictograms/4" with body:
    """
    {
      "description": "Picto updated",
      "category": "/engineering/pictogram/categories/4"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/pictogram/schemas/pictogram.json"
    And the JSON node "description" should be equal to the string "Picto updated"
    And the JSON node "category.name" should be equal to the string "Generator"

  Scenario: As a engineer, I can remove a pictogram
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/engineering/pictograms/5"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/engineering/pictograms/5"
    Then the response status code should be 404

  Scenario: As a basic user, I am not allow to create a pictogram
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/engineering/pictograms"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to update a pictogram
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/engineering/pictograms/1"
    Then the response status code should be 403

  Scenario: As a basic user, I am not allow to delete a pictogram
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/engineering/pictograms/1"
    Then the response status code should be 403