Feature: Quality / Location Area

  Scenario: As an anonymous user, test that i'm not allowed to see location area pages
    When I go to "/quality/location_areas"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/quality/location_areas/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see location area
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "quality/location_areas"
    Then the response status code should be 200
    And The module should be "CT"
    And I should see "TLD Location Areas"
    When I go to "/quality/location_areas/1/show"
    Then the response status code should be 200
    And I should see "Calibrated Tools"
    And I should not see an "a[href$='/quality/location_areas/1/edit']" element
    And I should not see an "a[href$='/quality/location_areas/1/delete']" element
    And I should see an "h5" element

  Scenario: As a basic user, test that i'm allowed to filter location area
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas?location_area%5Bname%5D=&location_area%5Bfactory%5D=&location_area%5Bsupervisor%5D=%2Fpeople%2F2&location_area%5BitemsPerPage%5D=&location_area%5B_token%5D=OmxVFC7CAShyl6A18ahsiWczQ_izVFrYmNE6ngIFW90"
    Then the response status code should be 200
    And I should see "TLD Location Areas"
    And I should see "LocationArea 2"

  Scenario: As a superuser, test that i'm allowed to see location area
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas"
    Then the response status code should be 200
    And I should see "Home"
    And I should see "Tools"
    And I should see "Location Areas"
    And I should see "Tool Types"
    And I should see "Out Of Tolerance Forms"
    And I should see an "a[href$='/quality/location_areas/add']" element
    And I should see "FILTER"
    When I go to "/quality/location_areas/1/show"
    Then the response status code should be 200
    And I should see an "a[href$='/quality/location_areas/1/edit']" element
    And I should not see an "a[href$='/quality/location_areas/1/delete']" element
    And I should see "Iasi"

  Scenario: As a superuser, test that i can add a location area
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas/add"
    Then the response status code should be 200
    When I fill in the following:
      | location_area[name]   | newLocationArea |
    And I select "/locations/29" from "location_area[factory]"
    And I select "/people/12" from "location_area[supervisor]"
    And press "submit"
    When the response status code should be 200
    And I should be on "/quality/location_areas/26/show"
    And I should see "The area has been created successfully."

  Scenario: As a superuser, test that i can edit a location area
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas/1/edit"
    Then the response status code should be 200
    And press "submit"
    Then the response status code should be 200
    And I should be on "/quality/location_areas/1/show"
    And I should see "The area has been modified."
    And I should see "Iasi"

  Scenario: As a superuser, test that i am not allowed to delete a location area
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas/1/delete"
    Then the response status code should be 200
    And I should be on "/quality/location_areas"
    And I should see "The area could not be removed."

  Scenario: As a superuser, test that i can delete a location area
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/quality/location_areas/5/delete"
    Then the response status code should be 200
    And I should be on "/quality/location_areas"
    And I should see "The area has been removed."
