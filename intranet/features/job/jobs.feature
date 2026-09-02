Feature: Jobs

  Scenario: As an anonymous user, test that i'm not allowed to see jobs pages
    When I go to "/human-resources/jobs"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/human-resources/jobs/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see jobs pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/jobs"
    Then the response status code should be 200
    When I go to "/human-resources/jobs/1/show"
    Then the response status code should be 200
    And The module should be "JOB"
    And I should see an "a[href$='/human-resources/jobs']" element
    And I should not see an "a[href$='/human-resources/jobs/1/add']" element
    And I should not see an "a[href$='/human-resources/jobs/1/edit']" element
    And I should not see an "a[href$='/human-resources/jobs/1/delete']" element

  Scenario: As a hr user, test that i'm allowed to see jobs pages
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/jobs"
    Then the response status code should be 200
    When I go to "/human-resources/jobs/1/show"
    Then the response status code should be 200
    And The module should be "JOB"
    And I should see an "a[href$='/human-resources/jobs']" element
    And I should see an "a[href$='/human-resources/jobs/add']" element
    And I should see an "a[href$='/human-resources/jobs/1/edit']" element
    And I should see an "a[href$='/human-resources/jobs/1/delete']" element

  Scenario: As a hr user, test that i can add a job
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/jobs/add"
    Then the response status code should be 200
    Then I select "FACTORY" from "job_form[businessUnit]"
    And I fill in "job_form[title]" with "ingenieur de surface"
    And I fill in "job_form[experience]" with "faire de beau centre"
    And I fill in "job_form[diploma]" with "presseur de citron a Sarcelle"
    And I fill in "job_form[description]" with "Je je suis un ingé, je suis un malin"
    And I check "job_form[enabled]"
    And I check "job_form[synchronized]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/human-resources/jobs/3/show"
    And I should see "A Job has been added successfully"
    And I should see "ingenieur de surface"

  Scenario: As a hr user, test that i can edit a job
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/jobs/1/edit"
    Then the response status code should be 200
    Then I select "FACTORY" from "job_form[businessUnit]"
    And I fill in "job_form[title]" with "New job mfb"
    And I fill in "job_form[experience]" with "une bonne dose de savoirfaire"
    And I fill in "job_form[diploma]" with "tailleur de pierre"
    And I fill in "job_form[description]" with "Il est venu le temps des cathedraaaaales"
    And I check "job_form[enabled]"
    And I check "job_form[synchronized]"
    And press "submit"
    Then the response status code should be 200
    And I should be on "/human-resources/jobs/1/show"
    And I should see "The Job has been edited successfully"
    And I should see "New job mfb"

  Scenario: A hr user user can delete a job
    Given I authenticate as "user-hr@tld.fr" with "P@ssw0rd15chars"
    When I go to "/human-resources/jobs/1/delete"
    Then the response status code should be 200
    And I should be on "/human-resources/jobs"
    And I should see "The Job has been deleted successfully"
