Feature: Test product certificates can be created and updated

  Scenario: Resource should only be accessible for intranet users
    Given I add "Accept" header equal to "application/ld+json"
    Then the resource "App\Entity\Sales\ProductCertificate" should only be available for intranet user

  Scenario: Filters are declared on resource
    Given the class "App\Entity\Sales\ProductCertificate" is exposed on the API
    Then the filter "product" should be available and its type should be "string"
    And the filter "product.family.productType" should be available and its type should be "string"
    And the filter "emissionRating" should be available and its type should be "string"
    And the filter "factory" should be available and its type should be "string"
    And the filter "expectedAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "expectedAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "expiredAt[before]" should be available and its type should be "DateTimeInterface"
    And the filter "expiredAt[after]" should be available and its type should be "DateTimeInterface"
    And the filter "order[expiredAt]" should be available and its type should be "string"

  Scenario: Product certificates should be filterable by basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates?q=TEST"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificates.json"

  Scenario: Product certificate detail should be accessible to intranet user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"

  Scenario: Create a Product certificate should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/product_certificates" with the body "tests/fixtures/json/sales/product_certificate/dummies/post.json"
    Then the response status code should be 403

  Scenario: Create a Product certificate should be possible for user em
    Given I authenticate as the intranet user "user-em@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/product_certificates" with the body "tests/fixtures/json/sales/product_certificate/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"
    And the JSON node "product.@id" should be equal to the string "/sales/products/5"
    And the JSON node "emissionRating.@id" should be equal to the string "/emission_ratings/3"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "engineeringActivityProcess" should be equal to the number 6969
    And the JSON node "announcementCertificateNumber" should be equal to the string "AC/DC"
    And the JSON node "url" should be equal to the string "http://www.certificat.test"
    And the JSON node "testReportNumber" should be equal to the string "TOTO"
    And the JSON node "description" should be equal to the string "Décris moi un mouton"

  Scenario: Create a Product certificate should be possible for user tsm
    Given I authenticate as the intranet user "user-tsm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/sales/product_certificates" with the body "tests/fixtures/json/sales/product_certificate/dummies/post.json"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"
    And the JSON node "product.@id" should be equal to the string "/sales/products/5"
    And the JSON node "emissionRating.@id" should be equal to the string "/emission_ratings/3"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "engineeringActivityProcess" should be equal to the number 6969
    And the JSON node "announcementCertificateNumber" should be equal to the string "AC/DC"
    And the JSON node "url" should be equal to the string "http://www.certificat.test"
    And the JSON node "testReportNumber" should be equal to the string "TOTO"
    And the JSON node "description" should be equal to the string "Décris moi un mouton"

  Scenario: Update a Product certificate should not be possible for basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/product_certificates/3" with the body "tests/fixtures/json/sales/product_certificate/dummies/put.json"
    Then the response status code should be 403

  Scenario: Update a Product certificate should be possible for user em
    Given I authenticate as the intranet user "user-em@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/product_certificates/3" with the body "tests/fixtures/json/sales/product_certificate/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"
    And the JSON node "product.@id" should be equal to the string "/sales/products/12"
    And the JSON node "emissionRating.@id" should be equal to the string "/emission_ratings/4"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "engineeringActivityProcess" should be equal to the number 666
    And the JSON node "announcementCertificateNumber" should be equal to the string "AC/DC UPDATE"
    And the JSON node "url" should be equal to the string "http://www.certificat.test.update"
    And the JSON node "testReportNumber" should be equal to the string "TOTO_UPDATED"
    And the JSON node "description" should be equal to the string "Description mise à jour"

  Scenario: Update a Product certificate should be possible for user tsm
    Given I authenticate as the intranet user "user-tsm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/sales/product_certificates/4" with the body "tests/fixtures/json/sales/product_certificate/dummies/put.json"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"
    And the JSON node "product.@id" should be equal to the string "/sales/products/12"
    And the JSON node "emissionRating.@id" should be equal to the string "/emission_ratings/4"
    And the JSON node "factory.@id" should be equal to the string "/locations/29"
    And the JSON node "engineeringActivityProcess" should be equal to the number 666
    And the JSON node "announcementCertificateNumber" should be equal to the string "AC/DC UPDATE"
    And the JSON node "url" should be equal to the string "http://www.certificat.test.update"
    And the JSON node "testReportNumber" should be equal to the string "TOTO_UPDATED"
    And the JSON node "description" should be equal to the string "Description mise à jour"

  @resetFileTable
  Scenario: As a basic user, I can't upload a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/product_certificates/2/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a superuser, I can upload a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/product_certificates/2/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/sales/product_certificates/2" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/sales/product_certificate/schemas/product_certificate.json"
    And the JSON node "files" should have 1 element

  Scenario: Upload an invalid file to a product certificate
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/sales/product_certificates/2/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a product certificate attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates/2/files/1"
    Then the response status code should be 200
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates/1/files/1"
    Then the response status code should be 404

  Scenario: As a basic user, I can't delete a file
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_certificates/2/files/1"
    Then the response status code should be 403

  Scenario: As a superuser, I can delete a file
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_certificates/1/files/1"
    Then the response status code should be 404
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/sales/product_certificates/2/files/1"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/sales/product_certificates/2" with a changeset on the property "files"
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/sales/product_certificates/2"
    And the JSON node "files" should have 0 element

