Feature: First Article Qualifications Plan Duplication

Scenario: A QAM duplicates an approved plan from the plan page
Given I authenticate as "user-qam@tld.fr" with "P@ssw0rd15chars"
When I go to "/quality/first-article-qualifications/18/plan"
Then I should see "PLAN DEFINITION"
And I should see "Duplicate plan"
When I select "/quality/first_article_qualifications/5" from "plan_duplicate[targets][]"
And I press "submit"
Then I should be on "/quality/first-article-qualifications/18/plan"
And I should see "The duplication completed successfully"