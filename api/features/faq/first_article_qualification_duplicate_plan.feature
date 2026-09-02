Feature: Duplicate an approved qualification plan to one or several FAQs

  # Fixtures assumed:
  # - FAQ 2:  NOT_APPROVED_YET, location_factory_2, empty plan
  # - FAQ 5:  NOT_APPROVED_YET, location_factory_2, empty plan
  # - FAQ 6:  NOT_APPROVED_YET, location_factory_2, empty plan
  # - FAQ 15: NOT_APPROVED_YET, location_factory,   empty plan (used to trigger different_factory)
  # - FAQ 18: APPROVED, location_factory_2, non-empty plan (invariant) — primary source
  # - FAQ 20: APPROVED, location_factory_2, non-empty plan (invariant) — target with plan

  Scenario: A user without the FAQ create feature is refused with a 403
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/18",
      "targets": ["/quality/first_article_qualifications/5"]
    }
    """
    Then the response status code should be 403

  Scenario: Sending no target FAQ is refused by the input DTO validation
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/18",
      "targets": []
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "ConstraintViolation"
    And the JSON node "hydra:description" should contain "Provide at least one target FAQ"

  Scenario: Duplicating from a plan that is not yet approved is refused with a 409
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/2",
      "targets": ["/quality/first_article_qualifications/5"]
    }
    """
    Then the response status code should be 409
    And the JSON node "@type" should be equal to "FirstArticleQualificationPlanDuplicationException"
    And the JSON node "detail" should contain "Only approved qualification plans can be duplicated"

  Scenario: A target on another factory is rejected with reason target_different_factory
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/18",
      "targets": ["/quality/first_article_qualifications/15"]
    }
    """
    Then the response status code should be 422
    And the JSON node "@type" should be equal to "FirstArticleQualificationPlanDuplicationException"
    And the JSON node "rejections" should have 1 elements
    And the JSON node "rejections[0].faq.@id" should be equal to "/quality/first_article_qualifications/15"
    And the JSON node "rejections[0].reason" should be equal to "target_different_factory"

  Scenario: A target that already carries a plan is rejected with reason target_has_plan
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/18",
      "targets": ["/quality/first_article_qualifications/20"]
    }
    """
    Then the response status code should be 422
    And the JSON node "rejections" should have 1 elements
    And the JSON node "rejections[0].faq.@id" should be equal to "/quality/first_article_qualifications/20"
    And the JSON node "rejections[0].reason" should be equal to "target_has_plan"

  Scenario: An approved plan is duplicated to eligible targets with completionRate reset
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/quality/first_article_qualifications/duplicate_plan" with body:
    """
    {
      "source": "/quality/first_article_qualifications/18",
      "targets": [
        "/quality/first_article_qualifications/5",
        "/quality/first_article_qualifications/6"
      ]
    }
    """
    Then the response status code should be 201
    And the JSON node "duplicatedFor" should have 2 elements
    And the JSON node "duplicatedFor[0]" should contain "/quality/first_article_qualifications/"
    # Verify one target actually received the plan with its per-item fields reset
    Given I authenticate as the intranet user "user-eng@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/quality/first_article_qualifications/5"
    Then the response status code should be 200
    And the JSON node "planApprovalStatus" should be equal to "NOT_APPROVED_YET"
    And the JSON node "planDefinitionCompletedAt" should be null
    And the JSON node "plan[0].completionRate" should be equal to the number 0
    And the JSON node "plan[0].validatedAt" should be null
    And the JSON node "plan[0].validatedBy" should be null