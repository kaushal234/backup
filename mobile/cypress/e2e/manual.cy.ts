import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";

const checkDetails = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.manual.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.manual.breadcrumbs[idx]);
    });

  cy.getBySel("manual-main-info-id-title").contains(
    TEXT.manual.details.id.title
  );
  cy.getBySel("manual-main-info-id-value").contains(
    TEXT.manual.details.id.value
  );

  cy.getBySel("manual-main-info-legacy-id-title").contains(
    TEXT.manual.details.legacyId.title
  );
  cy.getBySel("manual-main-info-legacy-id-value").contains(
    TEXT.manual.details.legacyId.value
  );

  cy.getBySel("manual-main-info-status-title").contains(
    TEXT.manual.details.status.title
  );
  cy.getBySel("manual-main-info-status-value").contains(
    TEXT.manual.details.status.value
  );

  cy.getBySel("manual-main-info-model-title").contains(
    TEXT.manual.details.model.title
  );
  cy.getBySel("manual-main-info-model-value").contains(
    TEXT.manual.details.model.value
  );

  cy.getBySel("manual-main-info-language-title").contains(
    TEXT.manual.details.language.title
  );
  cy.getBySel("manual-main-info-language-value").contains(
    TEXT.manual.details.language.value
  );

  cy.getBySel("manual-main-info-created-at-title").contains(
    TEXT.manual.details.createdAt.title
  );
  cy.getBySel("manual-main-info-created-at-value").contains(
    TEXT.manual.details.createdAt.value
  );

  cy.getBySel("manual-accordion-description-title").contains(
    TEXT.manual.details.description.title
  );
  cy.getBySel("manual-accordion-description-value").contains(
    TEXT.manual.details.description.value
  );

  cy.getBySel("manual-accordion-features-title").contains(
    TEXT.manual.details.features.title
  );
  cy.getBySel("manual-accordion-features-value").contains(
    TEXT.manual.details.features.value
  );

  cy.getBySel("manual-section-main-heading").contains(
    TEXT.manual.details.section.mainHeading
  );
  cy.getBySel("manual-section-manual-heading").contains(
    TEXT.manual.details.section.manual.heading
  );
  cy.getBySel("manual-section-manual-accordion-title")
    .contains(TEXT.manual.details.section.manual.accordionTitle)
    .click();
  cy.getBySel("manual-section-parts-heading").contains(
    TEXT.manual.details.section.parts.heading
  );
  cy.getBySel("manual-section-parts-accordion-title")
    .contains(TEXT.manual.details.section.parts.accordionTitle)
    .click();

  cy.getBySel("manual-section-manual-table-header")
    .findBySelLike("manual-section-manual-table-cell")
    .should("have.length", TEXT.manual.details.section.manual.headers.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.manual.details.section.manual.headers[idx]);
    });

  cy.getBySel("manual-section-manual-table-row-0")
    .findBySelLike("manual-section-manual-table-cell")
    .should("have.length", TEXT.manual.details.section.manual.rows.length)
    .each(($value, idx) => {
      if (idx === 0) {
        return;
      }
      cy.wrap($value).contains(TEXT.manual.details.section.manual.rows[idx]);
    });
};

const checkDownloadAndNavigation = () => {
  cy.getBySel("manual-section-manual-table-row-download-0").click();
  cy.waitForApiWithLoader(API_CALL.downloadManual, 200, null, true);
  cy.getBySel("manual-section-manual-table-row-view-0").click();
  cy.url().should("include", TEXT.manual.manualDocumentLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.downloadManual);
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/er/details/2/manual/2");
};

describe("Manual Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkDetails();
    checkDownloadAndNavigation();
  });
});
