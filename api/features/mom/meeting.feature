Feature: Test Meetings can be created and updated
  Scenario: Request all meetings without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings"
    Then the response status code should be 401

  Scenario: Request a single meeting without being authenticated should not be permitted
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/1"
    Then the response status code should be 401

  Scenario: Meetings should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/1"
    Then the response status code should be 403

  Scenario: Actions of meetings should not be accessible to XU
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/actions"
    Then the response status code should be 403
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/actions/1"
    Then the response status code should be 403

  Scenario: Basic user can list meetings but do not see confidential ones
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meetings.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Basic user can list actions of meeting but do not see confidential ones
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/actions"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/actions.json"
    And the JSON node "hydra:totalItems" should be equal to 1

  Scenario: COO user can list meetings and see confidential ones
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meetings.json"
    And the JSON node "hydra:totalItems" should be equal to 2

  Scenario: Basic user can see a non confidential meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"

  Scenario: Basic user can't see a confidential meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2"
    Then the response status code should be 403

  Scenario: The poster can see a confidential meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"

  Scenario: A notification is sent when a comment is added on an MOM through normal comments route
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/minutes_of_meeting/meetings/1",
      "message": "Coucou"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "MOM#1: a new comment has been added by SUPERUSER, user"
    And this asynchronous email should contain "Coucou"

  Scenario: A subscriber can see a confidential meeting
    Given I authenticate as the intranet user "user-coo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"

  Scenario: An attendee can see a confidential meeting
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MinutesOfMeeting\Meeting" is exposed on the API
    Then the filter "status" should be available and its type should be "string"
    And the filter "customers[]" should be available and its type should be "string"
    And the filter "competitors[]" should be available and its type should be "string"
    And the filter "order[createdAt]" should be available and its type should be "string"
    And the filter "productTypes[]" should be available and its type should be "string"
    And the filter "businessUnits[]" should be available and its type should be "string"
    And the filter "createdBy[]" should be available and its type should be "string"
    And the filter "subscribers" should be available and its type should be "string"

  Scenario: Meeting should be filterable
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings?q=spa&myTeam=1&mine=1"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meetings.json"

  Scenario: Filters are declared on resource
    Given the class "App\Entity\MinutesOfMeeting\Action" is exposed on the API
    Then the filter "task" should be available and its type should be "int"
    And the filter "order[createdAt]" should be available and its type should be "string"

  Scenario: Anyone can create a meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/minutes_of_meeting/meetings" with body:
    """
    {
      "title": "The spartan",
      "description": "This is madness ?",
      "location": "Spartatouille",
      "competitors": ["/sales/competitors/1"],
      "customers": ["/sales/customers/1"],
      "customerContacts": ["/sales/extranet_users/200"],
      "contacts": [
          {
              "firstName": "Carlos",
              "lastName": "Migouel",
              "phone": "12-12-12",
              "mail": "thisIsParta@sparta.com",
              "company": "Tirelipimpon"
          }
      ],
      "productTypes": ["/sales/product_types/2"],
      "attendees": ["/people/29"],
      "fullDescription": "les perses attaquent la grece qui pourra les sauver",
      "businessUnits": ["/business_units/1"],
      "meetingDate": "2019-12-06 10:12:25"
    }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And an email should have been sent asynchronously with subject matching pattern "/MOM#\S+ created by BASIC, user - The spartan/"
    And this asynchronous email should contain "A new meeting has been created by"
    And this asynchronous email should be sent to "user-basic@tld.fr"

  Scenario: The poster can see the contacts created
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "GET" request to "/minutes_of_meeting/contacts/2"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/contact.json"

  Scenario: the poster can update a meeting
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/1" with body:
    """
      {
        "title": "Coucou",
        "customers": ["/sales/customers/33"]
      }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And the JSON node "title" should be equal to "Coucou"
    And no email should have been sent asynchronously

  Scenario: The supervisor of the poster can update a meeting
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/1" with body:
    """
      {
        "title": "kikou",
        "customers": ["/sales/customers/34"]
      }
    """
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And the JSON node "title" should be equal to "kikou"

  Scenario: A basic user can't add a new action on a meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/minutes_of_meeting/actions" with body:
    """
      {
          "description": "Aisha ecoute moi",
          "assignee": "/people/11",
          "resource": "/minutes_of_meeting/meetings/2"
      }
    """
    Then the response status code should be 403

  Scenario: Some properties are mandatory to create an action
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/minutes_of_meeting/actions" with body:
    """
      {
      }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "violations[0].propertyPath" should be equal to "resource"
    And the JSON node "violations[0].message" should contain "This value should not be null."
    And the JSON node "violations[1].propertyPath" should be equal to "description"
    And the JSON node "violations[1].message" should contain "This value should not be blank."
    And the JSON node "violations[2].propertyPath" should be equal to "description"
    And the JSON node "violations[2].message" should contain "This value should not be null."
    And the JSON node "violations[3].propertyPath" should be equal to "assignee"
    And the JSON node "violations[3].message" should contain "This value should not be null."

  Scenario: Test the workflow of the meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "RELEASED"
    }
    """
    Then the response status code should be 200
    Then the JSON node "status" should be equal to "RELEASED"
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And an email should have been sent asynchronously with subject matching pattern "/MOM#\S+ status: RELEASED - Chuuuuuut/"
    And this asynchronous email should contain "The status of the meeting is now: RELEASED"
    #meeting is confidential so ASM should not be notified
    And this asynchronous email should not be sent to "user-sales@tld.fr"
    And this asynchronous email should be sent to "user-basic@tld.fr"
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/subscriptions" with body:
    """
    {
      "resource": "/minutes_of_meeting/meetings/2",
      "user": "/people/13"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "MOM#2 summary - Chuuuuuut"
    And this asynchronous email should be sent only to "user-hr@tld.fr"
    And the JSON should be valid according to the schema "tests/fixtures/json/subscription/schemas/subscription.json"
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "OPEN"
    }
    """
    Then the response status code should be 400
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/minutes_of_meeting/actions" with body:
    """
      {
          "description": "Philippe Carle best boss ever",
          "assignee": "/people/11",
          "resource": "/minutes_of_meeting/meetings/2",
          "metadata": {"internal": true}
      }
    """
    Then the response status code should be 201
    Then the JSON node "@id" should be equal to "/minutes_of_meeting/actions/4"
    Then the JSON node "internal" should be true
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/action.json"
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "CLOSED"
    }
    """
    Then the response status code should be 400
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/actions/4" with body:
    """
    {
      "completed": true,
      "closingComment": "(╯°□°)╯︵ ┻━┻"
    }
    """
    Then the response status code should be 200
    Then the JSON node "completed" should be true
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/action.json"
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "CLOSED"
    }
    """
    Then the response status code should be 200
    Then the JSON node "status" should be equal to "CLOSED"
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And an email should have been sent asynchronously with subject matching pattern "/MOM#\S+ status: CLOSED - Chuuuuuut/"
    And this asynchronous email should contain "The status of the meeting is now: CLOSED"
    And this asynchronous email should be sent to "user-asm@tld.fr"
    And this asynchronous email should be sent to "user-ceo@tld.fr"
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "RELEASED"
    }
    """
    Then the response status code should be 400
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "PUT" request to "/minutes_of_meeting/meetings/2/status" with body:
    """
    {
        "status": "OPEN"
    }
    """
    Then the response status code should be 400
    And no email should have been sent asynchronously

  @resetFileTable
  Scenario: As a meeting poster, I can upload a file to a public meeting
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/minutes_of_meeting/meetings/1/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"

  Scenario: As a basic user, I can't upload a file to a confidential meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/minutes_of_meeting/meetings/1/files" with file "file" "file.doc"
    Then the response status code should be 403

  Scenario: As a poster, I can upload a file to a confidential meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/minutes_of_meeting/meetings/2/files" with file "file" "file.doc"
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/shared/schemas/file.json"
    And an update log should have been inserted on resource "/minutes_of_meeting/meetings/2" with a changeset on the property "files"

  Scenario: Upload an invalid file to a meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/minutes_of_meeting/meetings/2/files" with file "file" "image.gif"
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "files: The mime type of the file is invalid"
    And the JSON node "violations[0].propertyPath" should be equal to "files"
    And the JSON node "violations[0].message" should contain "The mime type of the file is invalid"

  Scenario: Download a meeting public attached file as an extranet user should not be possible
    Given I authenticate as the extranet user "julien.lepers@tld.com"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/1/files/1"
    Then the response status code should be 404

  Scenario: Download a meeting public attached file as a basic user
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/1/files/1"
    Then the response status code should be 200

  Scenario: Download a meeting confidential attached file as a basic user should not be permitted
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2/files/2"
    Then the response status code should be 403

  Scenario: Download a meeting confidential attached file as a poster should be permitted
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2/files/2"
    Then the response status code should be 200

  Scenario: As a basic user, I can't delete a file from a confidential meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/minutes_of_meeting/meetings/2/files/2"
    Then the response status code should be 403

  Scenario: As a poster, I can delete a file from a confidential meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "DELETE" request to "/minutes_of_meeting/meetings/2/files/2"
    Then the response status code should be 204
    And an update log should have been inserted on resource "/minutes_of_meeting/meetings/2" with a changeset on the property "files"

  Scenario: generated files must be cleaned after tests
    Then I delete all the files created during test

  Scenario: a basic user delete a meeting
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/minutes_of_meeting/meetings/1"
    Then the response status code should be 403
    And the JSON node "hydra:description" should be equal to "Access Denied"

  Scenario: The supervisor of the poster can duplicate a meeting
    Given I authenticate as the intranet user "user-evp@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/json"
    When I send a "POST" request to "/minutes_of_meeting/meetings/2/duplicate" with body:
    """
      {
        "meetingDate": "2018-12-06 10:12:25",
        "description": "Calogera",
        "location": "Montaiguuu"
      }
    """
    Then the response status code should be 201
    And the JSON should be valid according to the schema "tests/fixtures/json/minutes_of_meeting/schemas/meeting.json"
    And the JSON node "title" should be equal to "Chuuuuuut"
    And the JSON node "createdBy.@id" should be equal to "/people/38"
    And the JSON node "createdAt" should contain today's date
    And the JSON node "location" should be equal to "Montaiguuu"
    And the JSON node "description" should be equal to "Calogera"
    And the JSON node "closedAt" should be null


  Scenario: the creator of the meeting delete the meeting
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/minutes_of_meeting/meetings/1"
    Then the response status code should be 204

  Scenario: Download a PDF summary of a meeting
    Given I authenticate as the intranet user "user-ceo@tld.fr"
    And I add "Accept" header equal to "application/pdf"
    When I send a "GET" request to "/minutes_of_meeting/meetings/2/summary"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/pdf"
    And the header "Content-Disposition" should be equal to 'inline; filename=meeting-0000002.pdf'
