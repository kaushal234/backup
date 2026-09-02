Feature: Sales Forecasts can be created and updated

  Scenario: A notification is sent when an SFR is created
    Given I authenticate as the intranet user "user-basic@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/master_sales_forecasts" with the body "tests/fixtures/json/sales/master_sales_forecast/dummies/post.json"
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject matching pattern "/AIR DE RIEN; Produit Test; 12; 04-2099; 1%; SFR#\S+ Created/"
    And this asynchronous email should be sent from "user-basic@tld.fr"
    And this asynchronous email should be sent to "user-asm@tld.fr"
    And this asynchronous email should be sent to "user-gcoo@tld.fr"
    And this asynchronous email should be sent to "user-gceo@tld.fr"
    And this asynchronous email should be sent to "user-chairman@tld.fr"
    And this asynchronous email should be sent to "user-csd@tld.fr"
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent to "user-pse@tld.fr"
    And this asynchronous email should be sent to "user-evp@tld.fr"
    And this asynchronous email should be sent as cc to "user-asm-transferred@tld.fr"

  Scenario: A notification is sent when an SFR is updated
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/2" with body:
    """
    {
      "sso": "/locations/28",
      "airport": "/airports/112",
      "product": "/sales/products/1",
      "quantity": 12,
      "comment": "right here, right now"
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "customer_35; Produit Test; 12; 02-2222; 72%; SFR#2 Updated"
    And this asynchronous email should be sent from "user-asm@tld.fr"
    # ASM
    And this asynchronous email should be sent to "user-basic@tld.fr"
    # ROLE_GCOO ROLE_GCEO ROLE_CHAIRMAN ROLE_GCH ROLE_GTD ROLE_CSD ROLE_GCFO
    And this asynchronous email should be sent to "user-gcoo@tld.fr"
    And this asynchronous email should be sent to "user-gceo@tld.fr"
    And this asynchronous email should be sent to "user-chairman@tld.fr"
    And this asynchronous email should be sent to "user-csd@tld.fr"
    # ROLE_COO ROLE_PSM ROLE_PSE ROLE_CEO
    And this asynchronous email should be sent to "user-psm@tld.fr"
    And this asynchronous email should be sent to "user-pse@tld.fr"
    # Customer-related contacts
    And this asynchronous email should be sent as cc to "user-cfo@tld.fr"
    # Supervisor of ASM
    And this asynchronous email should be sent to "user-evp@tld.fr"
    # Follower
    And this asynchronous email should be sent as cc to "user-mpe@tld.fr"
    # Sales Area ASM supervisor
    And this asynchronous email should be sent to "user-sam@tld.fr"
    And this asynchronous email should contain "right here, right now"
    And this asynchronous email should contain "Field quantity changed from <b>69</b> to <b>12</b>"
    And this asynchronous email should contain "Field sso changed from <b>location_sso</b> to <b>location_sso_2</b>"
    And this asynchronous email should contain "Field airport changed from <b>PLO</b> to <b>GRD</b>"
    And this asynchronous email should contain "Field product changed from <b>catalog_product_3</b> to <b>Produit Test</b>"
    And this asynchronous email should contain "/en/private/sales/sales-forecasts/2/show"

  Scenario: A notification is sent to a restricted list when an SFR is updated with notification restricted option
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/2" with body:
    """
    {
      "airport": "/airports/111",
      "comment": "on fait les bébés ?",
      "notificationRestricted": true
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "customer_35; Produit Test; 12; 02-2222; 72%; SFR#2 Updated"
    And this asynchronous email should be sent from "user-asm@tld.fr"
    And this asynchronous email should be sent only "To" "user-basic@tld.fr, user-asm@tld.fr, user-psm@tld.fr, user-evp@tld.fr, user-coo@tld.fr"
    And this asynchronous email should be sent as "Cc" only to "user-mpe@tld.fr"
    And this asynchronous email should contain "Field airport changed from <b>GRD</b> to <b>PLO</b>"
    And this asynchronous email should contain "on fait les bébés ?"
    And this asynchronous email should contain "Warning: Restricted notification"

  Scenario: A notification is sent when a comment is added on an SFR
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/2" with body:
    """
    {
      "comment": "hello"
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "customer_35; Produit Test; 12; 02-2222; 72%; SFR#2, New comment added"
    And this asynchronous email should be sent from "user-asm@tld.fr"
    And this asynchronous email should be sent as cc to "user-rceo@tld.fr"
    And this asynchronous email should contain "hello"

  Scenario: A notification is sent when a comment is added on an SFR through normal comments route
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/comments" with body:
    """
    {
      "resource": "/sales/sales_forecasts/2",
      "message": "Coucou"
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "customer_35; Produit Test; 12; 02-2222; 72%; SFR#2, New comment added"
    And this asynchronous email should be sent from "user-asm@tld.fr"
    And this asynchronous email should contain "Coucou"

  Scenario: No notifications are sent when a SFR couldn't not be closed
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/2/status" with body:
    """
    {
      "status": "LOST"
    }
    """
    Then the response status code should be 400
    And no email should have been sent asynchronously

  Scenario: A notification is sent when a SFR is closed and the last CPR has been posted
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/2/status" with body:
    """
    {
      "status": "ORDERED"
    }
    """
    Then the response status code should be 200
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with body:
    """
    {
      "forecastClosure": "/sales/forecast_closures/1",
      "quotationDate": "2050-12-25",
      "competitor": "/sales/competitors/1"
    }
    """
    Then the response status code should be 201
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "POST" request to "/sales/competitor_pricings" with body:
    """
    {
      "forecastClosure": "/sales/forecast_closures/1",
      "quotationDate": "2050-12-25",
      "competitor": "/sales/competitors/1",
      "last": true
    }
    """
    Then the response status code should be 201
    And an email should have been sent asynchronously with subject "customer_35; Produit Test; 12; 02-2222; 72%; SFR#2 Closed as ORDERED by MARTIN, Anne Sophie"
    And this asynchronous email should be sent from "user-asm@tld.fr"

  Scenario: A notification is sent when a SFR is closed and a specific route has been hit
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/9/status" with body:
    """
    {
      "status": "ORDERED"
    }
    """
    Then the response status code should be 200
    And no email should have been sent asynchronously
    Given I authenticate as the intranet user "user-asm@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/9/notify"
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "customer_14; Produit Test; 69; 06-2666; 72%; SFR#9 Closed as ORDERED by MARTIN, Anne Sophie"
    And this asynchronous email should be sent from "user-asm@tld.fr"

  Scenario: A notification is sent to a restricted list when an SFR is updated with notification restricted option
    Given I authenticate as the intranet user "user-superuser@tld.fr"
    And I add "Accept" header equal to "application/ld+json"
    And I add "Content-type" header equal to "application/ld+json"
    When I send a "PUT" request to "/sales/sales_forecasts/9" with body:
    """
    {
      "airport": "/airports/111",
      "comment": "how I supposed to know that something isn't right here ?",
      "notifyPackage": false
    }
    """
    Then the response status code should be 200
    And an email should have been sent asynchronously with subject "customer_14; Produit Test; 69; 06-2666; 72%; SFR#9 Updated"
    And this asynchronous email should be sent only "To" "user-asm@tld.fr, user-superuser@tld.fr,user-gcoo@tld.fr,user-gceo@tld.fr, user-chairman@tld.fr, user-csd@tld.fr, user-evp@tld.fr, user-pse@tld.fr, user-psm@tld.fr, user-ceo@tld.fr"
