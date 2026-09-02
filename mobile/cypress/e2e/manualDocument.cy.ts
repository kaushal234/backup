import { TEXT } from "../constants/strings";

const checkDetails = () => {
  cy.getBySel("breadcrumb-bar-link")
    .should("have.length", TEXT.manualDocument.breadcrumbs.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.manualDocument.breadcrumbs[idx]);
    });

  cy.getBySel("manual-document-main-info-position-title").contains(
    TEXT.manualDocument.details.position.title
  );
  cy.getBySel("manual-document-main-info-position-value").contains(
    TEXT.manualDocument.details.position.value
  );

  cy.getBySel("manual-document-main-info-document-number-title").contains(
    TEXT.manualDocument.details.documentNumber.title
  );
  cy.getBySel("manual-document-main-info-document-number-value").contains(
    TEXT.manualDocument.details.documentNumber.value
  );

  cy.getBySel("manual-document-main-info-factory-number-title").contains(
    TEXT.manualDocument.details.factoryNumber.title
  );
  cy.getBySel("manual-document-main-info-factory-number-value").contains(
    TEXT.manualDocument.details.factoryNumber.value
  );

  cy.getBySel("manual-document-main-info-revision-title").contains(
    TEXT.manualDocument.details.revision.title
  );
  cy.getBySel("manual-document-main-info-revision-value").contains(
    TEXT.manualDocument.details.revision.value
  );

  cy.getBySel("manual-document-main-info-type-title").contains(
    TEXT.manualDocument.details.type.title
  );
  cy.getBySel("manual-document-main-info-type-value").contains(
    TEXT.manualDocument.details.type.value
  );

  cy.getBySel("manual-document-main-info-category-title").contains(
    TEXT.manualDocument.details.category.title
  );
  cy.getBySel("manual-document-main-info-category-value").contains(
    TEXT.manualDocument.details.category.value
  );

  cy.getBySel("manual-document-accordion-description-title").contains(
    TEXT.manualDocument.details.description.title
  );
  cy.getBySel("manual-document-accordion-description-value").contains(
    TEXT.manualDocument.details.description.value
  );

  cy.getBySel("manual-document-accordion-other-description-title").contains(
    TEXT.manualDocument.details.otherDescription.title
  );
  cy.getBySel("manual-document-accordion-other-description-value").contains(
    TEXT.manualDocument.details.otherDescription.value
  );

  cy.getBySel("manual-document-download")
    .contains(TEXT.manualDocument.details.download)
    .click();

  cy.getBySel("manual-document-accordion-parts-list-title").contains(
    TEXT.manualDocument.details.parts.title
  );

  cy.getBySel("manual-document-table-header")
    .findBySelLike("manual-document-table-cell")
    .should("have.length", TEXT.manualDocument.details.parts.headers.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.manualDocument.details.parts.headers[idx]);
    });

  cy.getBySel("manual-document-table-row-0")
    .findBySelLike("manual-document-table-cell")
    .should("have.length", TEXT.manualDocument.details.parts.rows.length)
    .each(($value, idx) => {
      if (idx > 4) {
        return;
      }
      cy.wrap($value).contains(TEXT.manualDocument.details.parts.rows[idx]);
    });

  cy.getBySel("manual-document-table-row-0")
    .findByIconIdLike("CheckIcon")
    .should("have.length", 1);

  cy.getBySel("manual-document-table-row-0")
    .findByIconIdLike("ClearIcon")
    .should("have.length", 3);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser();
  cy.visit("/er/details/1/manual/1/document/1");
};

describe("Manual Document Page", () => {
  it("Basic Functionality", () => {
    setup();
    checkDetails();
  });
});
