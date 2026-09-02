Feature: Test deepl translator route
  Scenario: Translation can be used without module
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "deepl/translate" with body:
    """
    {
      "message": "你好吗",
      "moduleId": 12
    }
    """
    Then the response status code should be 200
    And the JSON node "translatedMessage" should be equal to the string "你好吗"
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "deepl/translate" with body:
    """
    {
      "message": "你好吗",
      "module": "PDC"
    }
    """
    Then the response status code should be 200
    #message is the same because deepl translator is not really called on test environment
    And the JSON node "translatedMessage" should be equal to the string "你好吗"

  Scenario: Module, message and moduleId should not be blank or null when using this route
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "deepl/translate" with body:
    """
    {
      "message": ""
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "message"
    And the JSON node "violations[0].message" should be equal to the string "This value should not be blank."

  Scenario: Formality should be a valid choice
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "deepl/translate" with body:
    """
    {
      "message": "my message",
      "formality": "street"
    }
    """
    Then the response status code should be 422
    And the JSON node "violations[0].propertyPath" should be equal to the string "formality"
    And the JSON node "violations[0].message" should be equal to the string "The value you selected is not a valid choice."

  Scenario: Translation can be used with formality parameter
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "deepl/translate" with body:
    """
    {
      "message": "my message",
      "formality": "prefer_more"
    }
    """
    Then the response status code should be 200