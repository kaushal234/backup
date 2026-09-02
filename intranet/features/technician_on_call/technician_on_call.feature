@toc
Feature: Technician On Call

  Scenario: As an anonymous user, test that i'm not allowed to see TOC pages
    When I go to "/service/technician-on-calls"
    Then I should be on "/login"
    And I should see "Password"
    When I go to "/service/technician-on-calls/1/show"
    Then I should be on "/login"
    And I should see "Password"

  Scenario: As a basic user, test that i'm allowed to see TOCs pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls"
    Then the response status code should be 200
    When I go to "/service/technician-on-calls/1/show"
    Then the response status code should be 200
    And The module should be "TOC"
    And I should see "First TOC"
    And I should see "0 hrs 0 min"
    And I should see "Time NMC"
    When I go to "/service/technician-on-calls/1/audit-log"
    Then the response status code should be 200

  Scenario: As a basic user, test TOC with migrated data works
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/18/show"
    Then the response status code should be 200
    When I go to "/service/technician-on-calls/19/show"
    Then the response status code should be 200
    When I go to "/service/technician-on-calls/20/show"
    Then the response status code should be 200

  Scenario: TOCs can be filtered
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls?technician_on_call_filter[id]=1&technician_on_call_filter[equipmentRecord]=/equipment_records/20&technician_on_call_filter[status][]=PENDING&technician_on_call_filter[assignee]=/people/65&technician_on_call_filter[unitOperationalStatus][]=/unit_operational_statuses/MCF&technician_on_call_filter[technicianOnCallType][]=/service/technician_on_call_types/1&technician_on_call_filter[serviceActivity][]=/service/service_activities/1&technician_on_call_filter[indiceFactor][]=IF 1&technician_on_call_filter[tags][]=/technician_on_call_tags/1&technician_on_call_filter[createdBy]=/people/32&technician_on_call_filter[createdAfter]=01/30/2025&technician_on_call_filter[createdBefore]=01/30/2025&technician_on_call_filter[salesOrganisation]=/locations/6&technician_on_call_filter[salesOrganisationService]=/locations/12&technician_on_call_filter[location]=/locations/9&technician_on_call_filter[productType][]=/sales/product_types/2&technician_on_call_filter[model][]=/sales/products/11&technician_on_call_filter[airport]=/airports/94"
    Then the response status code should be 200

  Scenario: As a basic user, test that i'm allowed to add a TOC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls"
    And I should see "Add"
    And I follow "Add"
    Then I should be on "/service/technician-on-calls/add"

  Scenario: As a basic user, test that i'm allowed to edit a TOC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/2/show"
    And I should see "Edit"
    And I follow "Edit"
    Then I should be on "/service/technician-on-calls/2/edit"

  Scenario: As a basic user, test that i'm allowed to see TOC reports page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/reports"
    Then the response status code should be 200
    Then I should be on "/service/technician-on-calls/reports"
    And I should see "TOC Count by SSO with Importance Factor (IF)"
    And I should see "TOC Count by SSO with Status"
    And I should see "TOC IN PROGRESS & SUSPENDED with Factory Support Required"
    And I should see "Survey average rating"

  Scenario: As a Basic User, I should be allowed to see TOC Surveys page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/surveys"
    Then the response status code should be 200
    And I should see "OKKKKK"

  Scenario: As a Basic User, I should be allowed to see TOC Email page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/1/email"
    Then the response status code should be 200
    And I should see "Send Email"

  @javascript
  Scenario: As a basic user, test that i'm not allowed to change status of TOC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/2/show"
    And I wait until I see "Change Status"

  @javascript
  Scenario: As a service user, test that i'm allowed to change status of TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/2/show"
    And I wait until I see "Update Status"
    And press "Update Status"
    When I focus on the select field ".select-status" and type "In Progress"
    And click on the 1st "#react-select-2-option-0" element
    And press "Submit"
    And I wait until I see "Saved"

  @javascript
  Scenario: As a service user, when I want to SUSPEND a TOC, some fields should be added but not required
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/4/show"
    And I wait until I see "Update Status"
    And press "Update Status"
    When I focus on the select field ".select-status" and type "Suspended"
    And click on the 1st "#react-select-2-option-1" element
    When I fill in the following:
      | reason          | my reason                        |
    And press "Submit"
    And I wait until I see "Saved"

  @javascript
  Scenario: As a service user, when I want to SOLVED a TOC, I should have errors if symptoms, root cause or solution are not filled
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/5/show"
    And I wait until I see "Update Status"
    And press "Update Status"
    When I focus on the select field ".select-status" and type "Solved"
    And click on the 1st "#react-select-2-option-0" element
    Then I should see an "button[disabled]" element

  @javascript
  Scenario: As a service user, when I want to SOLVED a TOC, some fields should be added
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/5/show"
    And I wait until I see "Update Status"
    And press "Update Status"
    When I focus on the select field ".select-status" and type "Solved"
    And click on the 1st "#react-select-2-option-0" element
    When I fill in the following:
      | originalSymptoms  | my symptoms                        |
      | originalRootCause | my root cause                     |
      | originalSolution  | my solution                        |
    And press "Submit"
    And I wait until I see "Saved"

  Scenario: As a basic user, when a TOC is closed, I should not change status
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/6/show"
    Then I should not see "Change Status To"

  @javascript
  Scenario: As a user-csm, test that i'm allowed to delete a TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/3/edit"
    And I should see "Delete"

  Scenario: As a basic user, test TOC file page when TOC not have file
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/1/files"
    Then I should see "No files found"

  Scenario: As a basic user, when TOC has CSR, I should see CSR information
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/8/show"
    Then I should see "Technician Requested"
    And I should see "CSR #20"
    And I should see an "a[href$='/service/customer-service-records/20/show']" element

  Scenario: As a basic user, I cannot create a CSR from TOC show page
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/9/show"
    Then I should see "Your current position does not allow you to create a CSR."

  Scenario: As a service user, I can create a CSR from TOC show page
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/11/show"
    Then I should see "Create CSR"
    And I press "technician_on_call_nested_customer_service_record_submit"
    Then I should be on "/service/technician-on-calls/11/show"
    And I should see "CSR #"

  Scenario: As a basic user, I should be able to see the tasks linked to the TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/1/tasks"
    And I should see "I am a TOC task"

  @javascript
  Scenario: As a basic user, I should be able to see the followers linked to the TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/1/follow"
    And I wait until I see "Add a follower"

  @javascript
  Scenario: As a basic user, I should be able to see the modules linked to the TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/25/links"
    And I should see "Links"
#    Not working on CI
#    And I wait until I see "PDC5002, PDC Short Description, PENDING"

  @javascript
  Scenario: As a service user, I can show KPI
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi"
    Then I should see "Quantity of TOC Open and Solved per"
    Then I should see "TOC Immediate Ratio (TIR) evolution"
    Then I should see "TOC Average Time (TAT) evolution"
    Then I should see "TOC Oldest (TOL)"

  @javascript
  Scenario: As a service user, I can show KPI backlogs
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi/backlog"
    Then I should see "TOC Backlog Evolution"

  @javascript
  Scenario: As a service user, I can show KPI remote or on site
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi/remote"
    Then I should see "All"
    And I should see "TOC Solved Remotly"
    And I should see "Type of Unit's models for CSR with multiple interventions"
    And I should see "TOC Solved or Closed with CSR"

  @javascript
  Scenario: As a service user, I can show Spare Parts KPI
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi/spare-part-request"
    Then I should see "All"
    Then I should see "TOC with SPR"
    Then I should see "Quantity of TOC solved with SPR shipped, Quantity of day to dispatch parts"

  @javascript
  Scenario: As a service user, I can show FactoryFlag by model report
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/kpi/factory-flag"
    Then I should see "Quantity of Factory flag raised"

  @javascript
  Scenario: As a basic user, I see a message to inform that no survey exist on close TOC
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/2/show"
    Then I press "open-survey-modal"
    And I wait until I see "No Survey found for this TOC"

  @javascript
  Scenario: As a basic user, I can show the survey result when exist
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/6/show"
    Then I press "open-survey-modal"
    And I wait until I see "How could we have done better?"

  @javascript
  Scenario: As a user-csm, I can see the full activity page of a TOC
    Given I authenticate as "user-csm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/7/activity"
    And I wait until I see "The field description has been changed from empty to This TOC has comments"

  Scenario: As user psm, I can access my preferences
    Given I authenticate as "user-psm@tld.fr" with "P@ssw0rd15chars"
    When I go to "/service/technician-on-calls/subscriptions"
    Then the response status code should be 200
    And I should see "No results"
    Then I fill in "technician_on_call_setting_indiceFactor" with "IF 1000"
    And I click on element ID "technician_on_call_setting_submit"
    Then I should be on "/service/technician-on-calls/subscriptions"
    And I should see "Subscriptions successfully updated."
    And I should see 2 "table.report-table tbody tr" elements
    Then I click on element ID "technician_on_call_setting_delete"
    Then I should be on "/service/technician-on-calls/subscriptions"
    And the response status code should be 200
    And I should see "No results"