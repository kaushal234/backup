Feature: Test SCARs

  Scenario: Resource should only be accessible for intranet and evendors users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Quality\SupplierCorrectiveActionRequest" should only be available for "intranet,evendors" user

  Scenario: Request all SCARs without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests"
    Then the response status code should be 401

  Scenario: Request a single SCAR without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 401

  Scenario: Request all SCARs as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests"
    Then the response status code should be 403

  Scenario: Request a single SCAR as extranet user should not be permitted
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 403

  Scenario: Request all SCARs as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_requests.json"

  Scenario: Request a single SCAR as intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "activity" should have 2 elements

  Scenario: Request all SCARs as not granted authorized application should not be permitted
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests"
    Then the response status code should be 403

  Scenario: Request a single SCAR as not granted authorized application should not be permitted
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 403

  Scenario: Request all SCARs as vendor user should be permitted but filtered
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_requests.json"
    And the JSON node "hydra:totalItems" should be equal to "2"

  Scenario: Request a single SCAR as vendor user should be permitted for allowed ones
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/2"
    Then the response status code should be 404

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Quality\SupplierCorrectiveActionRequest" is exposed on the API
    And the filter "iFactor" should be available and its type should be "string"
    And the filter "id" should be available and its type should be "int"
    And the filter "factory" should be available and its type should be "string"
    And the filter "factory.name" should be available and its type should be "string"
    And the filter "shortDescription" should be available and its type should be "string"
    And the filter "supplierNumber" should be available and its type should be "string"
    And the filter "supplierName" should be available and its type should be "string"
    And the filter "parts.partNumber" should be available and its type should be "string"
    And the filter "status" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "createdAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "createdAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "closedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "closedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "order[id]" should be available and its type should be "string"
    And the filter "order[closedAt]" should be available and its type should be "string"
    And the filter "order[shortDescription]" should be available and its type should be "string"
    And the filter "order[factory.name]" should be available and its type should be "string"
    And the filter "order[poster.lastname]" should be available and its type should be "string"
    And the filter "order[leader.lastname]" should be available and its type should be "string"
    And the filter "order[representative.lastname]" should be available and its type should be "string"
    And the filter "order[supplierNumber]" should be available and its type should be "string"
    And the filter "order[supplierName]" should be available and its type should be "string"
    And the filter "order[status]" should be available and its type should be "string"
    And the filter "order[iFactor]" should be available and its type should be "string"
    And the filter "normalizationGroups[]" should be available and its type should be "string"
    And the filter "normalizationGroupsOverride[]" should be available and its type should be "string"
    And the filter "poster" should be available and its type should be "string"
    And the filter "representative" should be available and its type should be "string"
    And the filter "q" should be available and its type should be "string"
    And the filter "columns" should be available and its type should be "string"

  Scenario: As basic user I can create a scar (but not with all properties)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests" with body:
    """
    {
      "iFactor": "IF1000",
      "factory": "/locations/30",
      "affectedFactories": ["/locations/29", "/locations/30"],
      "shortDescription": "this is a short description",
      "description": "this is a description",
      "representative": "/people/12",
      "leader": "/people/11",
      "supplierRepresentative": "/purchasing/vendor_users/301",
      "approvedAt": "2099-04-19",
      "createdAt": "2099-04-19",
      "closedAt": "2099-04-19",
      "issueOrigin": "route 66",
      "correctiveAction": "prendre la route 65",
      "commercialAgreement": "autoroute gratuite",
      "verificationDescription": "test de la route 66",
      "preventiveAction": "prendre la prochaine sortie",
      "conclusion": "appeler un taxi",
      "supplierNumber": "DAN0013",
      "supplierName": "CUMMINS Parent Test",
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "iFactor" should be equal to the string "IF1000"
    And the JSON node "factory.@id" should be equal to the string "/locations/30"
    And the JSON node "shortDescription" should be equal to the string "this is a short description"
    And the JSON node "description" should be equal to the string "this is a description"
    And the JSON node "representative.@id" should be equal to the string "/people/12"
    And the JSON node "poster.@id" should be equal to the string "/people/11"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "leader.@id" should be equal to the string "/people/11"
    And the JSON node "supplierRepresentative.@id" should be equal to the string "/purchasing/vendor_users/301"
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].@id" should be equal to the string "/parts/supplier_corrective_action_request_parts/9"
    And the JSON node "commercialAgreement" should be null
    And the JSON node "correctiveAction" should be null
    And the JSON node "verificationDescription" should be null
    And the JSON node "preventiveAction" should be null
    And the JSON node "conclusion" should be null
    And the JSON node "issueOrigin" should be null

  Scenario: As authorized app with no permission I can't create a scar
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests" with body:
    """
    {
      "iFactor": "IF1000",
      "factory": "/locations/30",
      "shortDescription": "this is a short description",
      "description": "this is a description",
      "representative": "/people/12",
      "leader": "/people/11",
      "supplierRepresentative": "/purchasing/vendor_users/301",
      "approvedAt": "2099-04-19",
      "createdAt": "2099-04-19",
      "closedAt": "2099-04-19",
      "issueOrigin": "route 66",
      "correctiveAction": "prendre la route 65",
      "commercialAgreement": "autoroute gratuite",
      "verificationDescription": "test de la route 66",
      "preventiveAction": "prendre la prochaine sortie",
      "conclusion": "appeler un taxi",
      "supplierName": "CUMMINS DIESEL SC",
      "supplierNumber": "CU5000",
      "posterInformation": "Tata Yoyo - keskiasoustongrandch@peau.com",
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 403

  Scenario: As vendor user with permission I can create a SCAR
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests" with body:
    """
    {
      "iFactor": "IF1000",
      "factory": "/locations/30",
      "shortDescription": "this is a short description",
      "description": "this is a description",
      "representative": "/people/12",
      "leader": "/people/11",
      "supplierRepresentative": "/purchasing/vendor_users/301",
      "approvedAt": "2099-04-19",
      "createdAt": "2099-04-19",
      "closedAt": "2099-04-19",
      "issueOrigin": "route 66",
      "correctiveAction": "prendre la route 65",
      "commercialAgreement": "autoroute gratuite",
      "verificationDescription": "test de la route 66",
      "preventiveAction": "prendre la prochaine sortie",
      "conclusion": "appeler un taxi",
      "supplierName": "CUMMINS DIESEL SC",
      "supplierNumber": "DAN0013",
      "vendorWarrantyClaims": ["/purchasing/ncr_vendor_warranty_claims/4"],
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "iFactor" should be equal to the string "IF1000"
    And the JSON node "factory.@id" should be equal to the string "/locations/30"
    And the JSON node "shortDescription" should be equal to the string "this is a short description"
    And the JSON node "description" should be equal to the string "this is a description"
    And the JSON node "representative" should be null
    And the JSON node "poster.@id" should be equal to the string "/purchasing/vendor_users/300"
    And the JSON node "createdAt" should be newer than 1 minute ago
    And the JSON node "status" should be equal to the string "PENDING"
    And the JSON node "leader" should be null
    And the JSON node "supplierRepresentative" should be null
    And the JSON node "supplierName" should be equal to the string "DANA SAS FRANCE"
    And the JSON node "supplierNumber" should be equal to "DAN0013"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].@id" should be equal to the string "/parts/supplier_corrective_action_request_parts/10"
    And the JSON node "commercialAgreement" should be null
    And the JSON node "correctiveAction" should be equal to the string "prendre la route 65"
    And the JSON node "verificationDescription" should be null
    And the JSON node "preventiveAction" should be null
    And the JSON node "conclusion" should be null
    And the JSON node "issueOrigin" should be equal to the string "route 66"
    And the JSON node "vendorWarrantyClaims[0].status.name" should be equal to the string "VALIDATE_SCAR"

  Scenario: Creating a scar should generate subscriptions
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/subscriptions?resource=/quality/supplier_corrective_action_requests/4"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements

  Scenario: A closed scar can't be updated
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/2" with body:
    """
    {
      "iFactor": "IF1000"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should be equal to the string "Closed SCAR can't be edited"

  Scenario: As basic user I can update a scar (except leader property)
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/4" with body:
    """
    {
      "iFactor": "IF100",
      "factory": "/locations/29",
      "shortDescription": "this is an updated short description",
      "description": "this is an updated description",
      "representative": "/people/13",
      "poster": "/people/14",
      "leader": "/people/12",
      "supplierRepresentative": "purchasing/vendor_users/302",
      "issueOrigin": "route 67",
      "correctiveAction": "prendre la route 66",
      "commercialAgreement": "autoroute gratuite mais pas trop",
      "verificationDescription": "test de la route 67",
      "preventiveAction": "prendre la prochaine sortie, ou la suivante",
      "conclusion": "appeler joe le taxi",
      "supplierNumber": "AMA0010",
      "supplierName": "DPE",
      "parts": [
        {
          "partNumber": "7400021",
          "description": "Willy Waller",
          "unitOfMeasure": "BTC",
          "quantity": 42,
          "comment": "avec ton Willi Waller Two Thousand Six là, jamais manger des bonnes patates aura été aussi facile"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "iFactor" should be equal to the string "IF100"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "shortDescription" should be equal to the string "this is an updated short description"
    And the JSON node "description" should be equal to the string "this is an updated description"
    And the JSON node "representative.@id" should be equal to the string "/people/13"
    And the JSON node "poster.@id" should be equal to the string "/people/11"
    And the JSON node "leader.@id" should be equal to the string "/people/11"
    And the JSON node "supplierRepresentative.@id" should be equal to the string "/purchasing/vendor_users/302"
    And the JSON node "supplierName" should be equal to the string "AMAZON"
    And the JSON node "supplierNumber" should be equal to the string "AMA0010"
    And the JSON node "parts" should have 1 element
    And the JSON node "parts[0].@id" should be equal to the string "/parts/supplier_corrective_action_request_parts/11"
    And the JSON node "commercialAgreement" should be equal to the string "autoroute gratuite mais pas trop"
    And the JSON node "correctiveAction" should be equal to the string "prendre la route 66"
    And the JSON node "verificationDescription" should be equal to the string "test de la route 67"
    And the JSON node "preventiveAction" should be equal to the string "prendre la prochaine sortie, ou la suivante"
    And the JSON node "conclusion" should be equal to the string "appeler joe le taxi"
    And the JSON node "issueOrigin" should be equal to the string "route 67"

  Scenario: As user qam I can update leader property
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/4" with body:
    """
    {
      "leader": "/people/12"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "leader.@id" should be equal to the string "/people/12"

  Scenario: As vendor user I can't update SCAR
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1" with body:
    """
    {
      "leader": "/people/12"
    }
    """
    Then the response status code should be 403

  Scenario: As basic user I can comment an open scar
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/quality/supplier_corrective_action_requests/1",
      "message": "test"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - New comment \/ Nouveau commentaire/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-basic@tld.fr"
    And this asynchronous email should not contain "Dear supplier"
    And the JSON node "metadata.recipients.0" should be equal to the string "user-basic@tld.fr"

  Scenario: As user qam I can comment a scar for conversation with supplier and it should change status
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                         |
      | resource        | /quality/supplier_corrective_action_requests/1 |
      | message         | this is a conversation                        |
      | discriminator   | scar_conversation                             |
      | metadata        | {"tos": ["test@tld.fr"]}                      |
      | file            | @file.pdf                                     |
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - New comment \/ Nouveau commentaire/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, test@tld.fr"
    And this asynchronous email should contain "Dear supplier"
    And this asynchronous email should have an attachment matching "/file.pdf/"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "quality/supplier_corrective_action_requests/1"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "VENDOR TO FILL FORM"
    And the JSON node "activity" should have 3 elements

  Scenario: As granted vendor user I can comment a scar for conversation and it should change status
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "VENDOR TO FILL FORM"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "VENDOR TO FILL FORM"
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                 |
      | resource        | /quality/supplier_corrective_action_requests/1        |
      | message         | this is a response from vendor                        |
      | discriminator   | scar_conversation                                     |
      | file            | @file.pdf                                             |
    Then the response status code should be 201
    And a total of 1 request has been sent to ION
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - New comment \/ Nouveau commentaire/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, vendor.user@vendor.fr"
    And this asynchronous email should contain "Dear TLD employee"
    And this asynchronous email should have an attachment matching "/file.pdf/"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "quality/supplier_corrective_action_requests/1"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "TLD TO REVIEW FORM"
    And the JSON node "activity" should have 4 element

  Scenario: As not granted authorized application I can' comment a scar for conversation
    Given I authenticate as the authorized application "La Poire Belle LN"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                 |
      | resource        | /quality/supplier_corrective_action_requests/2        |
      | message         | this is a response from vendor                        |
      | discriminator   | scar_conversation                                     |
      | metadata        | {"lastname": "eve", "firstname": "n'dors"}            |
      | file            | @file.pdf                                             |
    Then the response status code should be 403

  Scenario: As not granted vendor user I can' comment a scar for conversation
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                                 |
      | resource        | /quality/supplier_corrective_action_requests/2        |
      | message         | this is a response from vendor                        |
      | discriminator   | scar_conversation                                     |
      | file            | @file.pdf                                             |
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Item not found"

  Scenario: As user qam I can comment a scar for conversation with supplier when scar is closed
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                         |
      | resource        | /quality/supplier_corrective_action_requests/2 |
      | message         | this is a conversation                        |
      | discriminator   | scar_conversation                             |
      | metadata        | {"tos": ["test@tld.fr"]}                      |
      | file            | @file.pdf                                     |
    Then the response status code should be 403

  Scenario: As user basic I can't comment a scar for conversation with supplier
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/comments" with parameters:
      | key             | value                                         |
      | resource        | /quality/supplier_corrective_action_requests/1 |
      | message         | this is a conversation                        |
      | discriminator   | scar_conversation                             |
      | metadata        | {"tos": ["test@tld.fr"]}                      |
      | file            | @file.pdf                                     |
    Then the response status code should be 403

  Scenario: As basic user, I can't break scar status workflow
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "PENDING"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Status PENDING is not allowed."

  Scenario: As basic user, I can't change status to VALIDATION
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "VALIDATION"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Status VALIDATION is not allowed. Reasons: You do not have permissions to do this. Authorized group: SUPERUSER, ROLE_QAM, ROLE_QE, ROLE_QA"

  Scenario: As QAM user, I can change status to VALIDATION
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "VALIDATION"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node status should be equal to the string "VALIDATION"
    And the JSON node "approvedAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - VALIDATION/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-ceo@tld.fr"

  Scenario: As basic user, I can't change status to COMMERCIAL AGREEMENT
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "COMMERCIAL AGREEMENT"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "Status COMMERCIAL AGREEMENT is not allowed. Reasons: You do not have permissions to do this. Authorized group: SUPERUSER, ROLE_QAM, ROLE_COO, ROLE_CEO for ifactor 100 and 1000"
  Scenario: As user qam, I can change status to COMMERCIAL AGREEMENT
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "COMMERCIAL AGREEMENT"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node status should be equal to the string "COMMERCIAL AGREEMENT"
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - COMMERCIAL AGREEMENT/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-ceo@tld.fr"

  Scenario: As user qam, I can't change status to CLOSED
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 400
    And the JSON node "hydra:description" should contain "You do not have permissions to do this."

  Scenario: As user ceo, I can change status to CLOSED for scar of my factory
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node status should be equal to the string "CLOSED"
    And the JSON node "closedAt" should be newer than 1 minute ago
    And an email should have been sent asynchronously with subject matching pattern "/SCAR#\S+ - CLOSED/"
    And this asynchronous email should be sent only to "user-qam@tld.fr, user-em@tld.fr, user-mlm@tld.fr, user-ceo@tld.fr"

  Scenario: As user ceo, I can reopen a closed scar
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/supplier_corrective_action_requests/1/status" with body:
    """
    {
      "status": "VALIDATION"
    }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node status should be equal to the string "VALIDATION"

  Scenario: Request a single SCAR with a 'workflow' normalization group should add its available status to the response
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1?normalizationGroups[]=workflow"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/supplier_corrective_action_request/schemas/supplier_corrective_action_request.json"
    And the JSON node "availableStatus[0]" should be equal to the string "TLD TO REVIEW FORM"
    And the JSON node "availableStatus[1]" should be equal to the string "COMMERCIAL AGREEMENT"

  @resetFileTable
  Scenario: As a basic user, I can upload a file to a scar
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Upload an invalid file to a scar
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests/1/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a scar attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1/files/1"
    Then the response status code should be 403

  Scenario: Download a scar attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1/files/1"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file from a scar
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/1/files/1"
    Then the response status code should be 403

  Scenario: As a user rme, I can delete a file from a scar
    Given I authenticate as the intranet user "user-rme@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/1/files/1"
    Then the response status code should be 204

  Scenario: Change main file of scar with permission OK
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/supplier_corrective_action_requests/1/main_file" with file "file" "image_1200x1200.jpg"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: Basic users can't delete a scar main file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/1/main_file/2"
    Then the response status code should be 204
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests/1"
    And the response status code should be 200
    And the JSON node "mainFile" should be null

  Scenario: As basic user I can't delete a scar
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/3"
    Then the response status code should be 403

  Scenario: As vendor user I can't delete a scar
    Given I authenticate as the evendors user "vendor.user@vendor.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/1"
    Then the response status code should be 403

  Scenario: As user qam I can delete a scar of my factory
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/quality/supplier_corrective_action_requests/3"
    Then the response status code should be 204

  Scenario: User can download an Excel file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    When I send a "GET" request to "/quality/supplier_corrective_action_requests?columns=id,iFactor,factory.name,factory.erp,representative,poster,leader,status,supplierName,supplierNumber,createdAt,closedAt"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"
    And the xlsx file headers are:
      | Id | Indice Factor | Factory Name | Factory Erp | Representative | Poster | Leader | Status | Supplier Name | Supplier Number | Created At | Closed At |
    And the xlsx file should have 5 lines