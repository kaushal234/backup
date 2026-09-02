Feature: Test Crab Entity

  Scenario: Request all crabs
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crabs.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\Crab" is exposed on the API
    Then the filter "order[id]" should be available and its type should be "string"
    Then the filter "order[equipmentRecord.serialNumber]" should be available and its type should be "string"
    Then the filter "order[equipmentRecord.product.family.productType.englishName]" should be available and its type should be "string"
    Then the filter "order[equipmentRecord.manufacturerLocation.name]" should be available and its type should be "string"
    Then the filter "order[category]" should be available and its type should be "string"
    Then the filter "order[department.name]" should be available and its type should be "string"
    Then the filter "order[code.code]" should be available and its type should be "string"
    Then the filter "order[createdBy.lastname]" should be available and its type should be "string"
    Then the filter "order[createdAt]" should be available and its type should be "string"
    Then the filter "order[fixedBy.lastname]" should be available and its type should be "string"
    Then the filter "order[fixedAt]" should be available and its type should be "string"
    Then the filter "order[inspectedBy.lastname]" should be available and its type should be "string"
    Then the filter "order[inspectedAt]" should be available and its type should be "string"
    Then the filter "id" should be available and its type should be "int"
    Then the filter "createdBy" should be available and its type should be "string"
    Then the filter "equipmentRecord.product.family" should be available and its type should be "string"
    Then the filter "equipmentRecord.product.family.productType" should be available and its type should be "string"
    Then the filter "equipmentRecord.manufacturerLocation" should be available and its type should be "string"
    Then the filter "status" should be available and its type should be "string"
    Then the filter "part.partNumber" should be available and its type should be "string"
    Then the filter "equipmentRecord" should be available and its type should be "string"
    Then the filter "equipmentRecord.product" should be available and its type should be "string"
    Then the filter "equipmentRecord.serialNumber" should be available and its type should be "string"
    Then the filter "code" should be available and its type should be "string"
    Then the filter "department" should be available and its type should be "string"
    Then the filter "category" should be available and its type should be "string"
    Then the filter "legacyId" should be available and its type should be "int"
    Then the filter "derogation.status" should be available and its type should be "string"
    Then the filter "derogation" should be available and its type should be "string"
    Then the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    Then the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    Then the filter "exists[derogation]" should be available and its type should be "bool"
    Then the filter "q" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: Request a single crab
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\Crab" should only be available for "intranet" user

  Scenario: Add crab is not possible on a shipped equipment record
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/18",
      "part": null,
      "category": "Assy",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "firstArticleQualification": "/quality/first_article_qualifications/1",
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "equipmentRecord: Cannot create a CRAB on an Equipment Record that has been already shipped."


  Scenario: Add crab is not possible if code is 'FAQ  (First Article Qualification)' (5) and no Part Number
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "part": null,
      "category": "Assy",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/4",
      "firstArticleQualification": "/quality/first_article_qualifications/1",
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "code: Part number must be filled for code: 5 - FAQ  (First Article Qualification)."

  Scenario: As basic user, I can create a CRAB but not with all properties
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "part": {
          "partNumber": "PN752822",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
      },
      "category": "Assy",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "firstArticleQualification": "/quality/first_article_qualifications/1",
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab_post.json"
    And the JSON node "createdBy.@id" should be equal to the string "/people/11"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "status" should be equal to the string "TO-FIX"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "part.partNumber" should be equal to the string "PN752822"
    And the JSON node "category" should be equal to the string "Assy"
    And the JSON node "department.name" should be equal to the string "Paint"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "code.@id" should be equal to the string "/quality/crab_codes/1"
    And the JSON node "firstArticleQualification.@id" should be equal to the string "/quality/first_article_qualifications/1"
    And the JSON node "description" should be equal to the string "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 500,
          "project": [
            {
                "code": "THOMAS",
                "openCrabs": 1,
                "totalCrabs": 1
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: As a shopfloor user, i can update a given crab, but not all fields
    Given I authenticate as the intranet user "flore.shop"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "equipmentRecord": "/equipment_records/11",
        "part": {
          "reference": "PROD",
          "referenceNumber": "à moi",
          "serialNumber": "sn1234",
          "partNumber": "petgn1",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
        },
        "category": "Test",
        "department": "/quality/crab_departments/2",
        "nonConformity": "/quality/non_conformities/2",
        "code": "/quality/crab_codes/2",
        "firstArticleQualification": "/quality/first_article_qualifications/2",
        "description": "Un PUT pingouin.",
        "status": "TO-INSPECT",
        "fixingComments": "just for test"
      }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab.json"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/15"
    And the JSON node "part.partNumber" should be equal to the string "PN752822"
    And the JSON node "category" should be equal to the string "Assy"
    And the JSON node "department.@id" should be equal to the string "/quality/crab_departments/1"
    And the JSON node "nonConformity.@id" should be equal to the string "/quality/non_conformities/1"
    And the JSON node "code.@id" should be equal to the string "/quality/crab_codes/1"
    And the JSON node "description" should be equal to the string "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    And the JSON node "status" should be equal to the string "TO-INSPECT"
    And the JSON node "firstArticleQualification.@id" should be equal to the string "/quality/first_article_qualifications/2"
    And the JSON node "fixingComments" should be equal to the string "just for test"
    And no requests have been sent to ION

  Scenario: Reset CRAB for other tests
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "status": "TO-FIX",
        "fixingComments": null
      }
    """
    Then the response status code should be 200

  Scenario: Fix a CRAB is not possible without writing a comment
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "status" : "TO-INSPECT"
      }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "fixingComments: Comment is mandatory when fixing CRAB."

  Scenario: As user with permissions, I can fix a CRAB with a comment
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "status" : "TO-INSPECT",
        "fixingComments" : "je fixe mon crab"
      }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "TO-INSPECT"
    And the JSON node "fixedAt" should be newer than 1 minute ago

  Scenario: Inspected a CRAB is not possible without write a comment
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "status" : "CLOSED"
      }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "inspectingComments: Comment is mandatory when inspecting CRAB."

  Scenario: Edit Crab's fixingComments is not possible for a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "fixingComments" : "je modifie le commentaire de fixe de mon CRAB"
      }
    """
    And the response should be an error stating "Access Denied" with status code 403


  Scenario: Update a given crab - permissions OK for Full Write
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "status": "TO-INSPECT",
        "equipmentRecord": "/equipment_records/11",
        "part": {
          "partNumber": "petgn1",
          "description": "boulon",
          "quantity": 1,
          "unitOfMeasure": "EA"
        },
        "category": "Test",
        "department": "/quality/crab_departments/2",
        "nonConformity": "/quality/non_conformities/2",
        "code": "/quality/crab_codes/2",
        "description": "Un PUT pingouin."
      }
    """
    Then the response status code should be 200
    And the JSON node "department.name" should be equal to "Purchasing"
    And the JSON node "part.partNumber" should be equal to the string "petgn1"
    And the JSON node "part.description" should be equal to the string "boulon"
    And the JSON node "part.quantity" should be equal to the number 1
    And the JSON node "part.unitOfMeasure" should be equal to the string "EA"
    And the JSON node "equipmentRecord.@id" should be equal to the string "/equipment_records/11"
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 400,
          "project": [
            {
                "code": "REMI",
                "openCrabs": 1,
                "totalCrabs": 1
            }
          ]
        }
      }
    }
    """
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 500,
          "project": [
            {
                "code": "THOMAS",
                "openCrabs": 0,
                "totalCrabs": 0
            }
          ]
        }
      }
    }
    """
    And a total of 2 request has been sent to ION

  Scenario: Inspecting a CRAB is not possible if you are the same person who fixed it
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/4" with body:
    """
      {
        "inspectingComments" : "test Postman",
        "status" : "CLOSED"
      }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "A given CRAB cannot be fixed and inspected by the same person"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/4"
    And the JSON node "inspectedBy" should be null

  Scenario: Inspecting a CRAB is possible with permissions
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "status": "TO-FIX",
      "equipmentRecord": "/equipment_records/12",
      "category": "Assy",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "piQuestionId": 12345,
      "piQuestionParentId": 12,
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab_post.json"
    And the JSON node "piQuestionId" should be equal to the number 12345
    And the JSON node "piQuestionParentId" should be equal to the number 12
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/5" with body:
    """
      {
        "status" : "TO-INSPECT",
        "fixingComments" : "je fixe mon crab"
      }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/5" with body:
    """
      {
        "inspectingComments" : "test Postman",
        "status" : "CLOSED"
      }
    """
    Then the response status code should be 200
    And the JSON node "inspectingComments" should be equal to the string "test Postman"

  Scenario: Inspecting a CRAB is not possible even with permissions when Crab is PDI, PDI CSC ou PDI SOL
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "status": "TO-FIX",
      "equipmentRecord": "/equipment_records/12",
      "category": "PDI",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "piQuestionId": 12345,
      "piQuestionParentId": 12,
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab_post.json"
    And the JSON node "piQuestionId" should be equal to the number 12345
    And the JSON node "piQuestionParentId" should be equal to the number 12
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/6" with body:
    """
      {
        "status" : "TO-INSPECT",
        "fixingComments" : "je fixe mon crab"
      }
    """
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/6" with body:
    """
      {
        "inspectingComments" : "test Postman",
        "status" : "CLOSED"
      }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "A given CRAB cannot be fixed and inspected by the same person"

  Scenario: Update a CRAB is only possible for QAM if the CRAB is closed
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/2" with body:
    """
      {
        "description" : "test when CRAB Closed"
      }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Not possible to edit a closed CRAB."
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/2" with body:
    """
      {
        "description" : "test when CRAB Closed"
      }
    """
    Then the response status code should be 200
    And the JSON node "description" should be equal to the string "test when CRAB Closed"

  Scenario: Inspect a QA CRAB is not possible if I'm not from QA Team
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "category": "QA",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "description": "Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va."
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab_post.json"
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/7" with body:
    """
      {
        "status" : "TO-INSPECT",
        "fixingComments" : "je fixe mon QA crab"
      }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to "TO-INSPECT"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/7" with body:
    """
      {
        "status" : "CLOSED",
        "inspectingComments" : "inspect mon QA crab"
      }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Only a QA Team member can inspect a QA Crab"

  Scenario: Inspect a QA CRAB is possible for QA Team
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/crabs/7" with body:
    """
      {
        "status" : "CLOSED",
        "inspectingComments" : "inspect mon QA crab car je suis QA Team"
      }
    """
    Then the response status code should be 200
    And the JSON node "inspectingComments" should be equal to the string "inspect mon QA crab car je suis QA Team"
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 500,
          "project": [
            {
                "code": "THOMAS",
                "openCrabs": 0,
                "totalCrabs": 1
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Add crab from SOL is not possible if code is 'FAQ  (First Article Qualification)' (5) and no Part Number
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/add_from_order_line" with parameters:
      | key          | value  |
      | category     | Assy   |
      | code         |  /quality/crab_codes/4 |
      | department   |  /quality/crab_departments/1 |
      | description  | Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va. |
      | orderLine    | 18828 |
    Then the response status code should be 422
    And the JSON node "hydra:description" should be equal to the string "code: Part number must be filled for code: 5 - FAQ  (First Article Qualification)."

  Scenario: Add crab on multiple equipment records is possible from a SOL, but only on ER non shipped
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/add_from_order_line" with parameters:
      | key          | value  |
      | category     | Assy   |
      | code         |  /quality/crab_codes/1 |
      | department   |  /quality/crab_departments/1 |
      | description  | Un pingouin sur la banquise Se dandine, se dandine Glisse par-ci, glisse par-là Tombe dans l’eau Et puis s’en va. |
      | orderLine    | 18828 |
    Then the response status code should be 204
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 400,
          "project": [
            {
                "code": "REMI",
                "openCrabs": 2,
                "totalCrabs": 2
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Add crab on multiple equipment records with duplicate function
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs/duplicate" with body:
    """
      {
        "crab" : "/quality/crabs/7",
        "equipmentRecords" : ["/equipment_records/11", "/equipment_records/21"]
      }
    """
    Then the response status code should be 201
    And the JSON node "duplicateCrabId[0]" should be equal to 9
    And a message of class "App\Message\Quality\Crab\CrabWrite" should have been sent in the bus
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 400,
          "project": [
            {
                "code": "REMI",
                "openCrabs": 3,
                "totalCrabs": 3
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: Duplicate a CRAB on an equipment record whit shipped date should not be possible
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs/duplicate" with body:
    """
      {
        "crab" : "/quality/crabs/3",
        "equipmentRecords" : ["/equipment_records/21"]
      }
    """
    Then the response status code should be 201
    And the JSON node "errorForEquipment.LIGHT20" should be equal to the string "equipmentRecord: Cannot create a CRAB on an Equipment Record that has been already shipped."

  Scenario: As a basic user, I can't delete any crab
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/4"
    Then the response status code should be 403

  Scenario: As a product manager, I can delete assy crab
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "category": "Test",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "description": "Et quand je donne ma langue au chat, je vois les autres"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/10"
    Then the response status code should be 204
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 500,
          "project": [
            {
                "code": "THOMAS",
                "openCrabs": 0,
                "totalCrabs": 1
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION

  Scenario: As a control supervisor, I can delete test crab
    Given I authenticate as the intranet user "user-ps@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "category": "Test",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "description": "Tout prêt à se jeter sur moi, L O L I T A"
    }
    """
    Then the response status code should be 201
    And a "txProductionOrder" SOAP client has been created
    And this client has been called on the operation "txUpdateCrabs" with the following request:
    """
    {
      "DataArea": {
        "txProductionOrder": {
          "site": 500,
          "project": [
            {
                "code": "THOMAS",
                "openCrabs": 1,
                "totalCrabs": 2
            }
          ]
        }
      }
    }
    """
    And a total of 1 request has been sent to ION
    Given I authenticate as the intranet user "user-ps@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/11"
    Then the response status code should be 204

  Scenario: As a qualitician, I can delete any crab
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/4"
    Then the response status code should be 204

  Scenario: As a QAM, I can delete any crab
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/crabs" with body:
    """
    {
      "equipmentRecord": "/equipment_records/15",
      "category": "QA",
      "department": "/quality/crab_departments/1",
      "nonConformity": "/quality/non_conformities/1",
      "code": "/quality/crab_codes/1",
      "description": "C Moutet > all"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/12"
    Then the response status code should be 204

  Scenario: Crabs filtered can be downloaded as an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/quality/crabs?columns=id,status,equipmentRecord.manufacturerLocation.name,createdAt,createdBy,fixedAt,fixedBy,fixingComments,inspectedAt,inspectedBy,inspectingComments,description,stage,code,department,part.partNumber,equipmentRecord.serialNumber,equipmentRecord.model,questionParentId,questionSubject,questionDescription"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Status | Factory | Created At | Created By | Fixed At | Fixed By | Fixing Comments | Inspected At | Inspected By | Inspecting Comments | Description | Stage |  Code | Department | Pn | Serial Number | Model | Question Parent Id | Question Subject | Question Description |

  @resetFileTable
  Scenario: Upload a crab attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: Upload a crab attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a basic user, I can upload a file to a crab
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/1/files" with parameters:
      | key             | value                                         |
      | public          | 1                                             |
      | file            | @file.doc                                     |
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As user qam I can change visibility of a file of a crab
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab.json"
    And the JSON node "files[0].public" should be true
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": false
    }
    """
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/crab/schemas/crab.json"
    And the JSON node "files[0].public" should be false

  Scenario: As user basic I can't change visibility of a file of a crab
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/files/1" with body:
    """
    {
      "public": true
    }
    """
    Then the response status code should be 403

  Scenario: Upload an invalid file to a crab
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a crab attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1/files/1"
    Then the response status code should be 403

  Scenario: Download a crab attached file as a vendor user should not be possible
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1/files/1"
    Then the response status code should be 403

  Scenario: Download a crab attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1/files/1"
    Then the response status code should be 200

  Scenario: As a user basic, I can't delete a file from a crab
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/1/files/1"
    Then the response status code should be 403

  Scenario: As a qualitician, I can delete a file from a crab
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/crabs/1/files/1"
    Then the response status code should be 204

  Scenario: Change main file of a crab with permission OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/crabs/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: qualitician can delete a crab main file
    Given I authenticate as the intranet user "user-quality@tld.fr"
    When I send a "DELETE" request to "/quality/crabs/1/main_file/2"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-quality@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/crabs/1"
    And the response status code should be 200
    And the JSON node "mainFile" should be null
