import { TEXT } from "../constants/strings";

export const checkManualsEmptyTab = () => {
  cy.visit("/er/details/3");
  cy.getBySel("tab-manuals").click();
  cy.getBySel("er-manuals-no-content").contains(TEXT.erManual.noManuals);
};

const checkManualsTab = () => {
  cy.visit("/er/details/1");
  cy.getBySel("tab-manuals").click();
  cy.getBySel("manual-card").should("have.length.at.least", 1);

  cy.getBySel("manual-card")
    .first()
    .findBySel("manual-card-value")
    .should("have.length", TEXT.erManual.cardValues.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.erManual.cardValues[idx]);
    });
};

const checkNavigation = () => {
  cy.getBySel("manual-card").eq(0).click();
  cy.url().should("include", TEXT.erManual.cardLink);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.visit("/");
  cy.loginWithUser();
};

describe("ER Page", () => {
  it("Manual List Functionality", () => {
    setup();
    checkManualsEmptyTab();
    checkManualsTab();
    checkNavigation();
  });
});
