Feature: Test GitLab Merge Requests API
  # Collection endpoint provided by ApiPlatform DTO App\Dto\MIS\Gitlab\MergeRequest

  Scenario: Request all Merge Requests for a project without being authenticated should not be allowed
    Given I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/gitlab/projects/merge_requests"
    Then the response status code should be 401

  Scenario: Request all Merge Requests for a project without being authorized should not be allowed
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/gitlab/projects/merge_requests?itemsPerPage=1&page=1"
    Then the response status code should be 403

  Scenario: Filters are declared on resource
    Given the class "App\Dto\MIS\Gitlab\MergeRequest" is exposed on the API
    Then the filter "order[updated_at]" should be available and its type should be "string"
    Then the filter "merged_after" should be available and its type should be "string"
    Then the filter "merged_before" should be available and its type should be "string"
    Then the filter "author" should be available and its type should be "string"

  Scenario: Request all Merge Requests for a project
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/gitlab/projects/merge_requests?itemsPerPage=1&page=1"
    Then the response status code should be 400
    And the JSON node "detail" should be equal to the string "This endpoint currently supports only requests with pagination=false."

  Scenario: Request all Merge Requests for a project with no pagination
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/gitlab/projects/merge_requests?pagination=0&merged_after=2026-01-01&merged_before=2026-01-31"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/gitlab/merge_request/schemas/merge_requests.json"

  Scenario: Filtering Merge Requests by dates and ordering
    Given I authenticate as the intranet user "user-mism@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    When I send a "GET" request to "/gitlab/projects/merge_requests?pagination=0&merged_after=2026-02-01T00:00:00%2B00:00&merged_before=2026-03-01T00:00:00%2B00:00&order[updated_at]=desc"
    Then the response status code should be 200
    And the JSON should be valid according to the schema "tests/fixtures/json/gitlab/merge_request/schemas/merge_requests.json"

  Scenario: Creating Merge Requests through the API should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "POST" request to "/gitlab/projects/merge_requests" with body:
    """
    {"title": "foo"}
    """
    Then the response status code should be 405

  Scenario: Updating a Merge Request through the API should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "PUT" request to "/gitlab/projects/merge_requests/1" with body:
    """
    {"title": "bar"}
    """
    Then the response status code should be 404

  Scenario: Deleting a Merge Request through the API should not be possible
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-Type" header equal to "application/ld+json"
    When I send a "DELETE" request to "/gitlab/projects/merge_requests/1"
    Then the response status code should be 404
