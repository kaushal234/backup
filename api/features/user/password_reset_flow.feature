Feature: Test password reset flow

  Scenario: Try to request a reset with missing properties
    When I send a "POST" request to "/reset_password" with body:
    """
    {}
    """
    Then the response status code should be 422

  Scenario: Try to request a reset with invalid portal
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "email": "user-basic@tld.fr",
      "portal": "invalid"
    }
    """
    Then the response status code should be 422

  Scenario: Request a reset for an intranet user
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "email": "user-basic@tld.fr",
      "portal": "intranet"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should be sent to "user-basic@tld.fr"

  Scenario: Request a reset for an extranet user
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "email": "julien.lepers@tld.com",
      "portal": "extranet"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should be sent to "julien.lepers@tld.com"

  Scenario: Request a reset for an eVendors user
    When I send a "POST" request to "/reset_password" with body:
    """
    {
      "email": "devteam@tld-america.com",
      "portal": "evendors"
    }
    """
    Then the response status code should be 204
    And an email should have been sent asynchronously with subject "Reset Password Confirmation"
    And this asynchronous email should be sent to "devteam@tld-america.com"

  Scenario: Try to confirm with an invalid token
    When I send a "POST" request to "/reset_password_confirmation/11/0000" with body:
    """
    {
      "newPassword": "Sup3rS3cur3P@ssword!"
    }
    """
    Then the response status code should be 404