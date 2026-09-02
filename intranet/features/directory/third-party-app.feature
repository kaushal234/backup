Feature: Third party app on user profile
  @javascript
  Scenario: As a super user, I can confirmed grant access with comment and see member
    Given I authenticate as "user-superuser@tld.fr" with "P@ssw0rd15chars"
    When I go to "/directory/people/28/third_party_apps#updateTasksList"
    And I wait until I see "TPA Extended"
    When I click on the 1st "div[data-kreyu--data-table-bundle--bootstrap-modal-url-value$='/en/private/28/update_tasks/9/confirmed_modal']" element
    And I wait until I see "Yes, confirmed"
    When I fill in "update_task_confirmation[comment]" with "Test comment update task on profile page"
    And I click on the 1st "form[action$='/directory/people/28/third_party_apps/9/grant_access_accepted'] .btn[type=submit]" element
    And I wait until I see "Task granted access accepted successfully"
    When I go to "/directory/people/28/third_party_apps?filter_profile_update_task[status][value]=CONFIRMED#updateTasksList"
    And I wait until I see "Comment"
    And I click on the 1st "a[data-bs-target='#profile_update_task--row-action--comment--0']" element
    And I wait until I see "Test comment update task on profile page"
    When I go to "/directory/people/28/third_party_apps"
    And I wait until I see "TPA Extended"