Feature: Test FAQP API

  Scenario: FAQ cannot be qualified if the plan is empty
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 400
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/15"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: FAQ can be rejected if pending
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/29/status" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "REJECTED"

  Scenario: FAQ cannot be qualified if a plan is not validated
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/15/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 400
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/15"
    And the JSON node "status" should be equal to the string "PENDING"

  Scenario: A QAM can send to conditional a faq in progress
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/17/status" with body:
    """
    {
      "status": "CONDITIONAL"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CONDITIONAL"

  Scenario: A QAM can send to conditional a faq in progress not completed
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/17/status" with body:
    """
    {
      "status": "CONDITIONAL"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CONDITIONAL"

  Scenario: A QAM can send to rejected a faq in progress
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/18/status" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "REJECTED"

  Scenario: A QAM can send to qualified a faq in progress
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/19/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "QUALIFIED"
    And an email should have been sent asynchronously with subject matching pattern "/Tasks, New: #\S+ opened for BOB Patrick by BOB Patrick/"
    And this asynchronous email should be sent to "user-buyer@tld.fr"

  Scenario: A QAM can send to rejected a faq in conditional
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/20/status" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "REJECTED"

  Scenario: A PSE can send to qualified a faq in conditional if iFactor is 1 or 10
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/21/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "QUALIFIED"

  Scenario: A PSE can't change status is iFactor is above 10
    Given I authenticate as the intranet user "user-pse@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/22/status" with body:
    """
    {
      "status": "CONDITIONAL"
    }
    """
    Then the response status code should be 403

  Scenario: A QAM can send to conditional a faq in defined
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/22/status" with body:
    """
    {
      "status": "CONDITIONAL"
    }
    """
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CONDITIONAL"
    And an email should have been sent asynchronously with subject "Quality / First article qualification status change notification"

  Scenario: A QAM cannot change the status of rejected faq
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/25/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 400

  Scenario: A QAM can change the status of qualified faq to rejected
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/24/status" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 200

  Scenario: Poster/Buyer/Owner cannot send to rejected a faq in progress
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/22/status" with body:
    """
    {
      "status": "REJECTED"
    }
    """
    Then the response status code should be 403

  Scenario: Poster/Buyer/Owner cannot send to qualifies a faq in progress
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/quality/first_article_qualifications/22/status" with body:
    """
    {
      "status": "QUALIFIED"
    }
    """
    Then the response status code should be 403

  Scenario: a comment post on a conditional faq mustn't change the status
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/quality/first_article_qualifications/22",
      "message": "Coucou"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/22"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "CONDITIONAL"

  Scenario: Add a file or a comment cannot pass to in_progress a FAQ 100% completed/in_progress
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/quality/first_article_qualifications/30",
      "message": "Coucou"
    }
    """
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/30"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS / PLAN 100% COMPLETED"
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I add "Content-type" header equal to "multipart/form-data"
    When I send a "POST" request to "/quality/first_article_qualifications/30/files" with file "file" "file.pdf"
    Then the response status code should be 201
    Given I authenticate as the intranet user "user-qam@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/30"
    Then the response status code should be 200
    And the JSON node "status" should be equal to the string "IN PROGRESS / PLAN 100% COMPLETED"
