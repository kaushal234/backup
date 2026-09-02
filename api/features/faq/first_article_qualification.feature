Feature: Test FAQ API

  Scenario: Request all FAQ
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualifications.json"

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\FirstArticleQualification\FirstArticleQualification" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\FirstArticleQualification\FirstArticleQualification" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    Then the filter "id" should be available and its type should be "int"
    And the filter "poster" should be available and its type should be "string"
    And the filter "owner" should be available and its type should be "string"
    And the filter "buyer" should be available and its type should be "string"
    And the filter "planApprovalStatus" should be available and its type should be "string"
    And the filter "location" should be available and its type should be "string"
    And the filter "supplierNumber" should be available and its type should be "string"
    And the filter "supplierName" should be available and its type should be "string"
    And the filter "partNumbers.number" should be available and its type should be "string"
    And the filter "partNumbers.revision" should be available and its type should be "string"
    And the filter "partNumbers.description" should be available and its type should be "string"
    And the filter "eap" should be available and its type should be "int"
    And the filter "meap" should be available and its type should be "int"
    And the filter "exists[planDefinitionCompletedAt]" should be available and its type should be "bool"
    And the filter "iFactor" should be available and its type should be "string"
    And the filter "productFamily" should be available and its type should be "string"
    And the filter "location.name" should be available and its type should be "string"
    And the filter "tags.name" should be available and its type should be "string"
    And the filter "equipmentRecords.serialNumber" should be available and its type should be "string"
    And the filter "noPlan" should be available and its type should be "bool"

  Scenario: Request all FAQ filtered
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications?noOpenTasks=true"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualifications.json"

  Scenario: Request all FAQ filtered by normalization group for export
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications?normalizationGroupsOverride[]=faq_export"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualifications_export.json"

  Scenario: Request all FAQ with additional normalization group: people_photo
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications?normalization_groups[]=people_photo"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualifications_with_people_photo.json"

  Scenario: Request all FAQ with additional normalization group: faq_progress
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications?normalization_groups[]=faq_progress"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualifications_with_faq_progress.json"
    And less than 10 database queries must have been executed

  Scenario: Request one FAQ
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualification.json"

  Scenario: Request all FAQ Tags
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/tags?resourceType=FirstArticleQualificationTag"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualification_tags.json"

  Scenario: Create a new FAQ - Permissions invalid
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications" with the body "tests/fixtures/json/first_article_qualification/dummies/post.json"
    Then the response status code should be 403

  Scenario: an engineer create a FAQ with only the values required
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications" with the body "tests/fixtures/json/first_article_qualification/dummies/post.json"
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "location.@id" should be equal to the string "/locations/29"
    And the JSON node "partNumbers" should have 1 elements
    And the JSON node "partNumbers[0].number" should contain "007"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "owner.@id" should be equal to the string "/people/37"
    And the JSON node "planDefinitionDueDate" should contain "2059-04-19"
    And the JSON node "dueDate" should contain "2099-04-19"
    And the JSON node "eap" should be equal to the number 1234
    #User should do not fill this field
    And the JSON node "plan" should have 0 elements
    And the JSON node "poster.@id" should be equal to the string "/people/61"
    And the JSON node "createdAt" should contain today's date
    And the JSON node "completedAt" should be null
    And the JSON node "planDefinitionCompletedAt" should be null
    And an email should have been sent asynchronously with subject "A new FAQ has been created"
    And this asynchronous email should be sent only to "user-pm@tld.fr"
    # user-pm is the owner
    And this asynchronous email should be sent as cc only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-eng@tld.fr"

  Scenario: A buyer create a FAQ sending all values
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications" with the body "tests/fixtures/json/first_article_qualification/dummies/post_full.json"
    Then the response status code should be 201
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "buyer.@id" should be equal to the string "/people/60"
    And the JSON node "partNumbers[0].description" should be equal to the string "Bond James Band"
    And the JSON node "partNumbers[0].@id" should be equal to the string "/quality/first_article_qualifications_part_numbers/18"
    And the JSON node "tags" should have 1 elements
    And the JSON node "tags[0].@id" should be equal to "/quality/first_article_qualification_tags/8"
    And the JSON node "plan" should have 0 elements
    And the JSON node "eap" should be equal to the number 1234
    And the JSON node "meap" should be equal to the number 8552
    And the JSON node "equipmentRecords" should have 1 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to "/equipment_records/14"
    And the JSON node "deliverablesDueDate" should contain "2059-04-19"

  Scenario: A buyer can update a Part numbers description
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
    {
      "partNumbers": [
  		{
  		    "@id": "/quality/first_article_qualifications_part_numbers/1",
            "number": "ten",
            "revision": "J",
            "description": "testinde"
        }
  	  ]
    }
    """
    Then the response status code should be 200
    And the JSON node "partNumbers[0].number" should be equal to the string "ten"
    And the JSON node "partNumbers[0].revision" should be equal to the string "J"
    And the JSON node "partNumbers[0].description" should be equal to the string "testinde"
    And the JSON node "partNumbers[0].@id" should be equal to the string "/quality/first_article_qualifications_part_numbers/1"

  Scenario: update a faq with a wrong supplier number is not possible
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
    """
    {
        "supplierNumber": "jdzoqs"
    }
    """
    Then the response status code should be 422
    And the response should be in JSON
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "supplierNumber: The supplier jdzoqs does not exist."

  Scenario: create a FAQ by a buyer without inform the supplier number should be not possible
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications" with the body "tests/fixtures/json/first_article_qualification/dummies/buyer_post_without_suno.json"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "supplierNumber: This value should not be null"
    And the JSON node "hydra:description" should contain "supplierName: This value should not be null"

  Scenario: A basic user update a FAQ and should be not allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/12" with the body "tests/fixtures/json/first_article_qualification/dummies/put.json"
    Then the response status code should be 403

  Scenario: Supervisor of buyer should be allowed to update FAQ of his own buyers
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
    {
      "eap": 9999
    }
    """
    Then the response status code should be 200
    And the JSON node "eap" should be equal to the string "9999"
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
    """
    {
      "eap": 9999
    }
    """
    Then the response status code should be 403

  Scenario: A QAM update a FAQ and defines the plan
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with the body "tests/fixtures/json/first_article_qualification/dummies/put.json"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN_PROGRESS"
    And the JSON node "planDefinitionCompletedAt" should contain today's date
    And the JSON node "plan" should have 4 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/1"
    And the JSON node "plan[0].requestedPriorDelivery" should be true
    And the JSON node "plan[0].requestedAtPurchaseOrder" should be true
    And the JSON node "plan[0].validatedAt" should be null
    And the JSON node "plan[0].completionRate" should be equal to the number 0
    And the JSON node "plan[0].validatedBy" should be null
    And the JSON node "plan[1].completionRate" should be equal to the number 100
    And the JSON node "plan[1].validatedAt" should contain today's date
    And the JSON node "plan[1].validatedBy.@id" should be equal to the string "/people/29"
    And the JSON node "plan[2].type.@id" should be equal to the string "/quality/plan_item_types/3"
    And the JSON node "plan[2].requestedPriorDelivery" should be null
    And the JSON node "plan[2].requestedAtPurchaseOrder" should be null
    And the JSON node "plan[3].completionRate" should be equal to the number 100
    And the JSON node "plan[3].validatedAt" should contain today's date
    And the JSON node "plan[3].validatedBy.@id" should be equal to the string "/people/29"
    And the JSON node "meap" should be equal to the number 123456
    And the JSON node "equipmentRecords" should have 2 elements
    And the JSON node "equipmentRecords[0].@id" should be equal to "/equipment_records/11"
    And the JSON node "equipmentRecords[1].@id" should be equal to "/equipment_records/14"
    And the JSON node "deliverablesDueDate" should contain "2049-01-15"
    And an email should have been sent asynchronously with subject matching pattern "/Plan completed for FAQ#\S+/"
    And this asynchronous email should be sent only to "user-qe@tld.fr"

  Scenario: A QAM update a plan defined
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with the body "tests/fixtures/json/first_article_qualification/dummies/put_remove_plan_elements.json"
    Then the response status code should be 200
    And the JSON node "plan" should have 2 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/1"
    And the JSON node "plan[1].type.@id" should be equal to the string "/quality/plan_item_types/3"

  Scenario: A Basic user can validate a plan item and send FAQ to 100% completed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications_plan_items/3" with body:
    """
    {
	  "description": "Bordeciel",
	  "completionRate": 100
    }
    """
    Then the response status code should be 200
    And the JSON node "completionRate" should be equal to the number 100
    And the JSON node "description" should be equal to the string ""
    And the JSON node "validatedAt" should contain today's date
    And the JSON node "validatedBy" should be equal to the string "/people/11"
    And the JSON node "type.description" should be equal to the string "i'm a test"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    When I send a "GET" request to "/quality/first_article_qualifications/1"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS / PLAN 100% COMPLETED"

  Scenario: The engineer poster can add a plan item and 100% completed FAQ must pass IN_PROGRESS
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
    """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/1",
          "requestedPriorDelivery": false,
          "requestedAtPurchaseOrder": true
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 1 element
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/1"
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: The engineers can update the plan
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/4" with the body "tests/fixtures/json/first_article_qualification/dummies/put_remove_plan_elements.json"
    Then the response status code should be 200
    And the JSON node "plan" should have 2 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/1"
    And the JSON node "plan[1].type.@id" should be equal to the string "/quality/plan_item_types/3"

  Scenario: CMO update a FAQ and should be allowed
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3"
        }
      ],
      "planDefinitionDueDate": "2069-04-19"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 1 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/3"
    And the JSON node "planDefinitionDueDate" should contain "2069-04-19"

  Scenario: COO update a FAQ and should be allowed
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/1",
          "requestedPriorDelivery": true,
          "requestedAtPurchaseOrder": true
        }
      ],
      "planDefinitionDueDate": "2069-04-20"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 1 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/1"
    And the JSON node "planDefinitionDueDate" should contain "2069-04-20"

  Scenario: Super user update a FAQ and should be allowed
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3",
          "description": "It's amazing"
        }
      ],
      "planDefinitionDueDate": "2069-04-21"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 1 elements
    And the JSON node "plan[0].type.@id" should be equal to the string "/quality/plan_item_types/3"
    And the JSON node "plan[0].description" should be equal to the string "It's amazing"
    And the JSON node "planDefinitionDueDate" should contain "2069-04-21"

  Scenario: Update a plan item should create logs on FAQ resource
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
    """
    {
       "plan": [
        {
          "@id": "/quality/first_article_qualifications_plan_items/25",
          "description": "A log should be created and attached to the FAQ."
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 1 elements
    And the JSON node "plan[0].description" should be equal to the string "A log should be created and attached to the FAQ."
    And an update log should have been inserted on resource "/quality/first_article_qualifications/3" with a changeset on the property "/quality/first_article_qualifications_plan_items/25 => description"

  Scenario: Plan description can be superior to 255 characters
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3",
          "description" : "Vous savez, moi je ne crois pas qu'il y ait de bonne ou de mauvaise situation. Moi, si je devais résumer ma vie aujourd'hui avec vous, je dirais que c'est d'abord des rencontres. Des gens qui m'ont tendu la main, peut-être à un moment où je ne pouvais pas, où j'étais seul chez moi. Et c'est assez curieux de se dire que les hasards, les rencontres, forgent une destinée... Parce que quand on a le goût de la chose, quand on a le goût de la chose bien faite, le beau geste, parfois on ne trouve pas l'interlocuteur en face je dirais, le miroir qui vous aide à avancer. Alors ça n'est pas mon cas, comme je disais là, puisque moi au contraire, j'ai pu : et je dis merci à la vie, je lui dis merci, je chante la vie, je danse la vie... je ne suis qu'amour ! Et finalement, quand beaucoup de gens aujourd'hui me disent « Mais comment fais-tu pour avoir cette humanité ? », et bien je leur réponds très simplement, je leur dis que c'est ce goût de l'amour ce goût donc qui m'a poussé aujourd'hui à entreprendre une construction mécanique, mais demain qui sait ? Peut-être simplement à me mettre au service de la communauté, à faire le don, le don de soi..."
        }
      ]
    }
    """
    Then the response status code should be 200

  Scenario: the buyer of an faq updates it and should be allowed but cannot edit the plan.
    Given I authenticate as the intranet user "user-mpe@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3"
        }
      ],
      "dueDate": "3000-04-21"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 0 elements
    And the JSON node "dueDate" should contain "3000-04-21"

  Scenario: the poster of an faq updates it and should be allowed but cannot edit the plan.
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3"
        }
      ],
      "dueDate": "3001-04-21"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 0 elements
    And the JSON node "dueDate" should contain "3001-04-21"

  Scenario: the owner of an faq updates it and should be allowed but cannot edit the plan.
    Given I authenticate as the intranet user "user-pm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
   """
    {
      "plan": [
        {
          "type": "/quality/plan_item_types/3"
        }
      ],
      "dueDate": "3002-04-21"
    }
    """
    Then the response status code should be 200
    And the JSON node "plan" should have 0 elements
    And the JSON node "dueDate" should contain "3002-04-21"

  Scenario: Update a defined Plan by an empty Plan is not allowed
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/3" with body:
    """
    {
      "plan": []
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "plan: You cannot reset a plan"

  Scenario: A basic user delete a FAQ and should be not allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/11"
    Then the response status code should be 403

  Scenario: A CMO delete a FAQ
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/11"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/faq_plan_items/12"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/faq_part_numbers/15"
    Then the response status code should be 404

  Scenario: Create a comment should update status to in progress
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/quality/first_article_qualifications/3",
      "message": "Coucou"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/activity_comment/schemas/comment.json"
    And an email should have been sent asynchronously with subject "New FAQ comment"
    And this asynchronous email should be sent only to "user-qam@tld.fr"
    And this asynchronous email should be sent as cc only to "user-eng@tld.fr, user-csd@tld.fr"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/3"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN_PROGRESS"

  Scenario: the owner can update tags linked to an FAQ
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/16" with body:
    """
      {
        "tags":[
          "/quality/first_article_qualification_tags/8"
          ]
      }
    """
    Then the response status code should be 200
    And the JSON node "tags" should have 1 elements
    And the JSON node "tags[0].@id" should be equal to "/quality/first_article_qualification_tags/8"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: The poster can update tags linked to an FAQ
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/16" with body:
    """
      {
        "tags":[
          "/quality/first_article_qualification_tags/8",
          "/quality/first_article_qualification_tags/9"
          ]
      }
    """
    Then the response status code should be 200
    And the JSON node "tags" should have 2 elements
    And the JSON node "tags[0].@id" should be equal to "/quality/first_article_qualification_tags/8"
    And the JSON node "tags[1].@id" should be equal to "/quality/first_article_qualification_tags/9"

  Scenario: The COO can update tags linked to an FAQ
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/16" with body:
    """
      {
        "tags":[
          "/quality/first_article_qualification_tags/9"
          ]
      }
    """
    Then the response status code should be 200
    And the JSON node "tags" should have 1 elements
    And the JSON node "tags[0].@id" should be equal to "/quality/first_article_qualification_tags/9"

  Scenario: The COO can update tags linked to an FAQ
    Given I authenticate as the intranet user "user-buyer@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/16" with body:
    """
      {
        "tags":[
          "/quality/first_article_qualification_tags/9"
          ]
      }
    """
    Then the response status code should be 200
    And the JSON node "tags" should have 1 elements
    And the JSON node "tags[0].@id" should be equal to "/quality/first_article_qualification_tags/9"

  Scenario: The COO can update members
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
    """
      {
        "members":[]
      }
    """
    Then the response status code should be 200

  Scenario: The CMO can update members
    Given I authenticate as the intranet user "user-cmo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
    """
      {
        "members":[]
      }
    """
    Then the response status code should be 200

  Scenario: The QAM can update members
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15" with body:
    """
      {
        "members":[]
      }
    """
    Then the response status code should be 200

  Scenario: Update request on contional FAQ without any change should not throw
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/20" with body:
    """
      {
        "eap": 123
      }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualification.json"

  Scenario: Update conditionnal FAQ linked entity should not be possible
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/20" with body:
    """
      {
        "tags":[
          "/quality/first_article_qualification_tags/9",
          "/quality/first_article_qualification_tags/12",
          "/quality/first_article_qualification_tags/13"
          ]
      }
    """
    Then the response status code should be 400
    And the JSON node "@type" should be equal to "hydra:Error"
    And the JSON node "hydra:description" should contain "You cannot update faq in status:"

  Scenario: Update qualified FAQ should be possible only for QAM
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/24" with body:
    """
      {
        "eap": 999,
        "plan": [
           {
             "type": "/quality/plan_item_types/3"
           }
         ],
        "partNumbers": [
            {
              "number": "ten",
              "revision": "J",
              "description": "testinde"
            }
        ]
      }
    """
    Then the response status code should be 200

  Scenario: Basic user cannot approve plan definition
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
      {
        "planApprovalStatus": "APPROVED"
      }
    """
    Then the response status code should be 403

  Scenario: QAM can approve plan definition
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
      {
        "planApprovalStatus": "APPROVED"
      }
    """
    Then the response status code should be 200
    And the JSON node "planApprovalStatus" should be equal to "APPROVED"
    And an email should have been sent asynchronously with subject "Plan APPROVED for FAQ 1"
    And this asynchronous email should be sent only to "user-eng@tld.fr"

  Scenario: QAM cannot send wrong plan approval status
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
      {
        "planApprovalStatus": "LOOOOOOOLLLLL"
      }
    """
    Then the response status code should be 422
    And the JSON node "hydra:description" should contain "planApprovalStatus: The value you selected is not a valid choice"

  Scenario: QAM can unapprove plan definition
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/1" with body:
    """
      {
        "planApprovalStatus": "UNAPPROVED"
      }
    """
    Then the response status code should be 200
    And the JSON node "planApprovalStatus" should be equal to "UNAPPROVED"
    And an email should have been sent asynchronously with subject "Plan UNAPPROVED for FAQ 1"
    And this asynchronous email should be sent only to "user-eng@tld.fr"

  Scenario: QAM cannot approve plan definition is the plan is not define
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/2" with body:
    """
    {
      "planApprovalStatus": "APPROVED"
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "planApprovalStatus: The plan must be defined to be APPROVED or UNAPPROVED"

  Scenario: Upload a file to FAQ without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/15/files" with file "file" "file.pdf"
    Then the response status code should be 403
    And no email should have been sent asynchronously

  @resetFileTable
  Scenario: Upload a file to FAQ with permission will update the status
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/12/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an email should have been sent asynchronously with subject "New file added on FAQ#12"
    And this asynchronous email should be sent only to "user-qam@tld.fr"
    And this asynchronous email should be sent as cc only to "user-buyer@tld.fr, user-eng@tld.fr, user-mlm@tld.fr"
    # Check that the item schema is still valid
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/12"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/first_article_qualification/schemas/first_article_qualification.json"
    And the JSON node "status" should be equal to "IN_PROGRESS"

  Scenario: Download a FAQ file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/12/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/15/files/1"
    Then the response status code should be 404

  @resetFileTable
  Scenario: Upload a file to FAQ with permission will create logs for file creation
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/12/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/quality/first_article_qualifications/12" with a changeset on the property "files"

  Scenario: Upload an invalid file to FAQ
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/12/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Delete an attached file to FAQ without permission
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/12/files/1"
    Then the response status code should be 403

  Scenario: Delete an attached file to FAQ with permission
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/12/files/15"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-mlm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/12/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/quality/first_article_qualifications/12" with a changeset on the property "files"
    Then I delete all the files created during test

  Scenario: Upload a file to REJECTED FAQ is forbidden
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/25/files" with file "file" "file.pdf"
    Then the response status code should be 400

  Scenario: Upload a file to QUALIFIED FAQ is not forbidden
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/24/files" with file "file" "file.pdf"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    Then I delete all the files created during test

  @resetFileTable
  Scenario: Delete a file to REJECTED FAQ is forbidden
    Given I have a file "App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile" from path "tests/fixtures/file.csv" for "/quality/first_article_qualifications/25"
    Then I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/first_article_qualifications/25/files/1"
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "This action is disabled when FAQ is REJECTED"
    Then I delete all the files created during test
