import { API_CALL } from "../constants/api";
import { TEXT } from "../constants/strings";

export const checkSchematicsEmptyTab = () => {
  cy.visit("/er/details/1");
  cy.getBySel("tab-schematics").click();
  cy.getBySel("er-schematics-no-content").contains(
    TEXT.erSchematics.noSchematics
  );
};

const checkSchematics = () => {
  cy.visit("/er/details/25");
  cy.getBySel("tab-schematics").click();

  cy.getBySel("er-schematics-heading").contains(
    TEXT.erSchematics.schematic.heading
  );

  cy.getBySel("er-schematics-card-0")
    .findBySel("er-schematics-card-0-value")
    .should("have.length", TEXT.erSchematics.schematic.cardValues.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(TEXT.erSchematics.schematic.cardValues[idx]);
    });

  cy.getBySel("er-schematics-card-0-download").click();
  cy.waitForApiWithLoader(API_CALL.downloadSchematic, null, null, true);
};

const checkExtraSchematics = () => {
  cy.visit("/er/details/18");
  cy.getBySel("tab-schematics").click();

  cy.getBySel("er-extra-schematics-heading").contains(
    TEXT.erSchematics.extraSchematic.heading
  );

  cy.getBySel("er-extra-schematics-card-0")
    .findBySel("er-extra-schematics-card-0-value")
    .should("have.length", TEXT.erSchematics.extraSchematic.cardValues.length)
    .each(($value, idx) => {
      cy.wrap($value).contains(
        TEXT.erSchematics.extraSchematic.cardValues[idx]
      );
    });

  cy.getBySel("er-extra-schematics-card-0-download").click();
  cy.waitForApiWithLoader(API_CALL.downloadSchematic, null, null, true);
};

const setup = () => {
  cy.clearIndexedDB();
  cy.interceptApi(API_CALL.downloadSchematic);
  cy.visit("/");
  cy.loginWithUser();
};

describe("ER Page", () => {
  it("Schematics List Functionality", () => {
    setup();
    checkSchematicsEmptyTab();
    checkSchematics();
    checkExtraSchematics();
  });
});
