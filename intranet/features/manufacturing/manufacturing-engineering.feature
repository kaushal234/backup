Feature: Manufacturing engineering

  Scenario: As an anonymous user, test that i'm not allowed to see drawings
    When I go to "/manufacturing/engineering/drawing/500/1051213/2022-01-31/drawing"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to download engineering drawing
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/engineering/drawing/500/1051213/2022-08-24"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "application/pdf"
    And I should see response headers "content-disposition" with 'inline; filename=1051213_I2.pdf'

  Scenario: As a basic user, test that i'm allowed to download a zip of engineering drawings
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/manufacturing/engineering/zip/500/1051213/2022-08-24"
    Then the response status code should be 200
    Then I should see response headers "content-type" with "application/zip"
    And I should see response headers "content-disposition" with 'inline; filename=1051213.zip'
