Feature: Communication

  Scenario: As a basic user, test that i'm allowed to see communication pages
    Given I authenticate as "user-basic@tld.fr" with "P@ssw0rd15chars"
    When I go to "communication/alvest-boiler-plates"
    Then the response status code should be 200
    And I should see "ALVEST Boiler Plates"
    When I go to "communication/alvest-business-cards"
    Then the response status code should be 200
    And I should see "ALVEST Business Cards"
    When I go to "communication/alvest-corporate-brochure"
    Then the response status code should be 200
    And I should see "ALVEST Corporate Brochure"
    When I go to "communication/alvest-email-signatures"
    Then the response status code should be 200
    And I should see "ALVEST Email Signatures"
    When I go to "communication/alvest-group-values"
    Then the response status code should be 200
    And I should see "ALVEST Group Values"
    When I go to "communication/alvest-logos"
    Then the response status code should be 200
    And I should see "ALVEST Logos"
    When I go to "communication/alvest-powerpoint-templates"
    Then the response status code should be 200
    And I should see "ALVEST Powerpoint Templates"
    When I go to "communication/aero-specialties-social-media"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Social Media"
    When I go to "communication/aero-specialties-boiler-plates"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Boiler Plates"
    When I go to "communication/aero-specialties-brand-guidelines"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Brand Guidelines"
    When I go to "communication/aero-specialties-business-cards"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Business Cards"
    When I go to "communication/aero-specialties-group-values"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Group Values"
    When I go to "communication/aero-specialties-colour-palette"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Colour Palette"
    When I go to "communication/aero-specialties-logos"
    Then the response status code should be 200
    And I should see "AERO SPECIALTIES Logos"
    When I go to "communication/aes-boiler-plates"
    Then the response status code should be 200
    And I should see "AES Boiler Plates"
    When I go to "communication/aes-brand-guidelines"
    Then the response status code should be 200
    And I should see "AES Brand Guidelines"
    When I go to "communication/aes-business-cards"
    Then the response status code should be 200
    And I should see "AES Business Cards"
    When I go to "communication/aes-email-signatures"
    Then the response status code should be 200
    And I should see "AES Email Signatures"
    When I go to "communication/aes-group-values"
    Then the response status code should be 200
    And I should see "AES Group Values"
    When I go to "communication/aes-logos"
    Then the response status code should be 200
    And I should see "AES Logos"
    When I go to "communication/aes-powerpoint-templates"
    Then the response status code should be 200
    And I should see "AES Powerpoint Templates"
    When I go to "communication/aes-social-media"
    Then the response status code should be 200
    And I should see "AES Social Media"
    When I go to "communication/aes-wallpapers"
    Then the response status code should be 200
    And I should see "AES Wallpapers"
    When I go to "communication/aaes-boiler-plates"
    Then the response status code should be 200
    And I should see "AAES Boiler Plates"
    When I go to "communication/aaes-business-cards"
    Then the response status code should be 200
    And I should see "AAES Business Cards"
    When I go to "communication/aaes-colour-palette"
    Then the response status code should be 200
    And I should see "AAES Colour Palette"
    When I go to "communication/aaes-email-signatures"
    Then the response status code should be 200
    And I should see "AAES Email Signatures"
    When I go to "communication/aaes-group-values"
    Then the response status code should be 200
    And I should see "AAES Group Values"
    When I go to "communication/aaes-logos"
    Then the response status code should be 200
    And I should see "AAES Logos"
    When I go to "communication/aaes-powerpoint-templates"
    Then the response status code should be 200
    And I should see "AAES Powerpoint Templates"
    When I go to "communication/aaes-social-media"
    Then the response status code should be 200
    And I should see "AAES Social Media"
    When I go to "communication/anderson-airmotive-boiler-plates"
    Then the response status code should be 200
    And I should see "ANDERSON AIRMOTIVE Boiler Plates"
    When I go to "communication/anderson-airmotive-email-signatures"
    Then the response status code should be 200
    And I should see "ANDERSON AIRMOTIVE Email Signatures"
    When I go to "communication/anderson-airmotive-group-values"
    Then the response status code should be 200
    And I should see "ANDERSON AIRMOTIVE Group Values"
    When I go to "communication/anderson-airmotive-logos"
    Then the response status code should be 200
    And I should see "ANDERSON AIRMOTIVE Logos"
    When I go to "communication/anderson-airmotive-social-media"
    Then the response status code should be 200
    And I should see "ANDERSON AIRMOTIVE Social Media"
    When I go to "communication/page-gse-social-media"
    Then the response status code should be 200
    And I should see "PAGE GSE Social Media"
    When I go to "communication/page-gse-boiler-plates"
    Then the response status code should be 200
    And I should see "PAGE GSE Boiler Plates"
    When I go to "communication/page-gse-colour-palette"
    Then the response status code should be 200
    And I should see "PAGE GSE Colour Palette"
    When I go to "communication/page-gse-group-values"
    Then the response status code should be 200
    And I should see "PAGE GSE Group Values"
    When I go to "communication/page-gse-logos"
    Then the response status code should be 200
    And I should see "PAGE GSE Logos"
    When I go to "communication/page-gse-social-media"
    Then the response status code should be 200
    And I should see "PAGE GSE Social Media"
    When I go to "communication/sage-parts-boiler-plates"
    Then the response status code should be 200
    And I should see "SAGE PARTS Boiler Plates"
    When I go to "communication/sage-parts-brand-guidelines"
    Then the response status code should be 200
    And I should see "SAGE PARTS Brand Guidelines"
    When I go to "communication/sage-parts-business-cards"
    Then the response status code should be 200
    And I should see "SAGE PARTS Business Cards"
    When I go to "communication/sage-parts-group-values"
    Then the response status code should be 200
    And I should see "SAGE PARTS Group Values"
    When I go to "communication/sage-parts-logos"
    Then the response status code should be 200
    And I should see "SAGE PARTS Logos"
    When I go to "communication/sage-parts-powerpoint-templates"
    Then the response status code should be 200
    And I should see "SAGE PARTS Powerpoint Templates"
    When I go to "communication/sage-parts-social-media"
    Then the response status code should be 200
    And I should see "SAGE PARTS Social Media"
    When I go to "communication/sage-parts-wallpapers"
    Then the response status code should be 200
    And I should see "SAGE PARTS Wallpapers"
    When I go to "communication/sas-boiler-plates"
    Then the response status code should be 200
    And I should see "SAS Boiler Plates"
    When I go to "communication/sas-business-cards"
    Then the response status code should be 200
    And I should see "SAS Business Cards"
    When I go to "communication/sas-group-values"
    Then the response status code should be 200
    And I should see "SAS Group Values"
    When I go to "communication/sas-logos"
    Then the response status code should be 200
    And I should see "SAS Logos"
    When I go to "communication/sas-social-media"
    Then the response status code should be 200
    And I should see "SAS Social Media"
    When I go to "communication/sas-wallpapers"
    Then the response status code should be 200
    And I should see "SAS Wallpapers"
    When I go to "communication/tld-boiler-plates"
    Then the response status code should be 200
    And I should see "TLD Boiler Plates"
    When I go to "communication/tld-business-cards"
    Then the response status code should be 200
    And I should see "TLD Business Cards"
    When I go to "communication/tld-corporate-brochure"
    Then the response status code should be 200
    And I should see "TLD Corporate Brochure"
    When I go to "communication/tld-email-signatures"
    Then the response status code should be 200
    And I should see "TLD Email Signatures"
    When I go to "communication/tld-group-values"
    Then the response status code should be 200
    And I should see "TLD Group Values"
    When I go to "communication/tld-logos"
    Then the response status code should be 200
    And I should see "TLD Logos"
    When I go to "communication/tld-powerpoint-templates"
    Then the response status code should be 200
    And I should see "TLD Powerpoint Templates"
    When I go to "communication/tld-social-media"
    Then the response status code should be 200
    And I should see "TLD Social Media"
    When I go to "communication/tld-wallpapers"
    Then the response status code should be 200
    And I should see "TLD Wallpapers"
    When I go to "communication/tld-songs"
    Then the response status code should be 200
    And I should see "TLD Songs"
    When I go to "communication/tld-posters"
    Then the response status code should be 200
    And I should see "TLD Posters"
    When I go to "communication/tld-data-sheets"
    Then the response status code should be 200
    And I should see "TLD Data Sheets"
    When I go to "communication/tld-fact-sheets"
    Then the response status code should be 200
    And I should see "TLD Fact Sheets"
    When I go to "communication/tld-linkedin-banners"
    Then the response status code should be 200
    And I should see "TLD LinkedIn Banners"
    When I go to "communication/tld-letterheads"
    Then the response status code should be 200
    And I should see "TLD Letterheads"
    When I go to "communication/tld-wollard-boiler-plates"
    Then the response status code should be 200
    And I should see "TLD WOLLARD Boiler Plates"
    When I go to "communication/tld-wollard-group-values"
    Then the response status code should be 200
    And I should see "TLD WOLLARD Group Values"
    When I go to "communication/tld-wollard-logos"
    Then the response status code should be 200
    And I should see "TLD WOLLARD Logos"
    When I go to "communication/tld-wollard-social-media"
    Then the response status code should be 200
    And I should see "TLD WOLLARD Social Media"
    When I go to "communication/xops-boiler-plates"
    Then the response status code should be 200
    And I should see "XOPS Boiler Plates"
    When I go to "communication/xops-business-cards"
    Then the response status code should be 200
    And I should see "XOPS Business Cards"
    When I go to "communication/xops-email-signatures"
    Then the response status code should be 200
    And I should see "XOPS Email Signatures"
    When I go to "communication/xops-group-values"
    Then the response status code should be 200
    And I should see "XOPS Group Values"
    When I go to "communication/xops-logos"
    Then the response status code should be 200
    And I should see "XOPS Logos"
    When I go to "communication/xops-powerpoint-templates"
    Then the response status code should be 200
    And I should see "XOPS Powerpoint Templates"
    When I go to "communication/xops-social-media"
    Then the response status code should be 200
    And I should see "XOPS Social Media"
